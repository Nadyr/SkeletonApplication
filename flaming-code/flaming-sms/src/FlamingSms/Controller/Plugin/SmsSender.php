<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use FlamingSms\Service\SmsService;
use FlamingSms\Entity\OutgoingSmsInterface;

/**
 * SmsSender
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class SmsSender extends AbstractPlugin
{
	/**
	 *
	 * @var SmsService
	 */
	protected $smsService;

	public function __construct(SmsService $smsService = null)
	{
		if (null !== $smsService)
			$this->setSmsService($smsService);
	}

	public function sendSms($to, $content, $senderId)
	{
		$sms = array(
			'gatewayName' => $this->getSmsService()->getDefaultGatewayName(),
			'receiver' => $to,
			'senderId' => $senderId,
			'status' => OutgoingSmsInterface::STATUS_QUEUED,
			'content' => $content
		);

		return $this->getSmsService()->createOutgoingSms($sms);
	}

	/**
	 *
	 * @return SmsService
	 */
	public function getSmsService()
	{
		return $this->smsService;
	}

	/**
	 *
	 * @param SmsService $smsService
	 * @return SmsSender
	 */
	public function setSmsService(SmsService $smsService)
	{
		$this->smsService = $smsService;
		return $this;
	}
}