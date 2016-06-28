<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\SmsGateway;

use FlamingSms\Entity\SmsInterface;

/**
 * FakeGw SMS Gateway
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class FakeGw extends AbstractGateway
{
	/**
	 *
	 * @var string
	 */
	protected $username;
	
	/**
	 *
	 * @var string
	 */
	protected $password;

	/**
	 *
	 * @var string
	 */
	protected $notificationUrl;
	
	public function configure($config)
	{
		if (is_array($config) || $config instanceof \ArrayAccess) {
			if (array_key_exists('url', $config))
				$this->setUrl($config['url']);
			if (array_key_exists('username', $config))
				$this->setUsername($config['username']);
			if (array_key_exists('password', $config))
				$this->setPassword($config['password']);
			if (array_key_exists('notificationUrl', $config))
				$this->setNotificationUrl($config['notificationUrl']);
		} else
			throw new \InvalidArgumentException('$config must be array or implement ArrayAccess');
		return $this;
	}

	public function parseDeliveryReponse($response)
	{
		if(false !== stripos($response, '-')) {
			$boom = explode('-', $response);
			$status = $boom[0];
			$statuscode = $boom[1];
		} else {
			$statuscode = null;
		}

		$retval = null;
		switch($status) {
			// buffered - Beskeden er forsøgt leveret, vil blive forsøgt igen senere
			// Do nothing

			// received - Beskeden er modtaget
			case 'received':
				$retval = SmsInterface::STATUS_DELIVERED_TO_HANDSET;
				break;

			// rejected - Beskeden er afvist af SMSC'en, af forskellige årsager (Se statuscode for forklaring)
			case 'rejected':
				if (null != $statuscode)
				{
					switch($statuscode)
					{
						// 0	Unknown subscriber
						case 0:
							$retval = SmsInterface::STATUS_INVALID_RECIPIENT;
							break;

						// 10	Network time-out
						case 10:
							$retval = SmsInterface::STATUS_TIMEOUT;
							break;

						// 103	Call barred
						// 104	Operation barred
						case 103:
						case 104:
							$retval = SmsInterface::STATUS_BLOCKED;
							break;

						// 1	Service temporary not available
						// 2	Service temporary not available
						// 3	Service temporary not available
						// 4	Service temporary not available
						// 5	Service temporary not available
						// 6	Service temporary not available
						// 7	Service temporary not available
						// 8	Service temporary not available

						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
							// Do nothing
							break;

						// 9	Illegal error code
						// 50	Error in MS
						// 100	Facility not supported
						// 101	Unknown subscriber
						// 102	Facility not provided
						// 105	SC congestion
						// 106	Facility not supported
						// 107	Absent subscriber
						// 108	Delivery fail
						// 109	SC congestion
						// 110	Protocol error
						// 111	MS not equipped
						// 112	Unknown SC
						// 113	SC congestion
						// 114	Illegal MS
						// 115	MS not a subscriber
						// 116	Error in MS
						// 117	SMS lower layer not provisioned
						// 118	System fail
						// 119	PLMN system failure
						// 120	HLR system failure
						// 121	VLR system failure
						// 122	Previous VLR system failure
						// 123	Controlling MSC system failure
						// 124	VMSC system failure
						// 125	EIR system failure
						// 126	System failure
						// 127	Unexpected data value
						// 200	Error in address service centre
						// 201	Invalid absolute Validity Period
						// 202	Short message exceeds maximum
						// 203	Unable to Unpack GSM message
						// 204	Unable to convert IRA ALPHANBET
						// 205	Invalid validity period format
						// 206	Invalid destination address
						// 207	Duplicate message submit
						// 208	Invalid message type indicator

						// Overtakserings relaterede fejlkoder
						// 104	Operation barred - Deres saldo er ikke høj nok til at gennemfør takseringen
						// 114	Illegal MS - Brugeren har spærret for overtaksering, enten tjenesten, shortcoden eller generelt
						default:
							$retval = SmsInterface::STATUS_NOT_DELIVERED;
							break;
					}
				}
				break;

			// expired - Gyldighedsperioden er udløbet, beskeden har ikke kunnet blive leveret
			case 'expired':
				$retval = SmsInterface::STATUS_TIMEOUT;
				break;
		}
		return $retval;
	}

	public function parseSendResponse($response)
	{
		$retval = null;

		$retArr = array();
		parse_str($response, $retArr);
		if ($retArr['status'] == 'success')
			$retval = SmsInterface::STATUS_SENT;
		else
		{
			switch($retArr['result']) {
				case 'Invalid mobile number':
					$retval = SmsInterface::STATUS_INVALID_RECIPIENT;
					break;

				default:
					$retval = SmsInterface::STATUS_QUEUED;
					break;
			}
		}
		return $retval;
	}

	public function send(SmsInterface $sms)
	{
		$live_url = $this->getUrl()
			  . "/?username=" . urlencode($this->getUsername())
			  . "&password=" . urlencode($this->getPassword())
			  . "&charset=utf-8&lang=en&resulttype=urlencoded"
			  . "&status=on&statusurl=" . urlencode($this->getNotificationUrl())
			  . "&to=" . urlencode($sms->getReceiver())
			  . "&from=" . urlencode(iconv('UTF-8', 'Windows-1252//TRANSLIT', $sms->getSenderId()))
			  . "&message=" . urlencode($sms->getContent());

		$response = @file_get_contents($live_url);

		$retval = false;
		if (null != ($id = $this->getGatewayId($response))) {
			$retval = true;
			$sms->setGatewayId($id);
			$sms->setStatus(SmsInterface::STATUS_SENT);
		} else {
			if (!$response) {
				$sms->setStatus(SmsInterface::STATUS_QUEUED);
			} else {
				$sms->setStatus($this->parseSendResponse($response));
				if($sms->getStatus() != SmsInterface::STATUS_QUEUED)
					$retval = true;
			}
		}

		return $retval;
	}

	private function getGatewayId($response)
	{
		$retArr = array();
		parse_str($response, $retArr);
		return !empty($retArr['msgid']) ? $retArr['msgid'] : null;
	}

	/**
	 * 
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * 
	 * @param string $username
	 * @return FakeGw
	 */
	public function setUsername($username)
	{
		$this->username = (string) $username;
		return $this;
	}

	/**
	 * 
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * 
	 * @param string $password
	 * @return FakeGw
	 */
	public function setPassword($password)
	{
		$this->password = (string) $password;
		return $this;
	}

	/**
	 * 
	 * @return string
	 */
	public function getNotificationUrl()
	{
		return $this->notificationUrl;
	}

	/**
	 * 
	 * @param string $url
	 * @return FakeGw
	 */
	public function setNotificationUrl($url)
	{
		$this->notificationUrl = (string) $url;
		return $this;
	}
}