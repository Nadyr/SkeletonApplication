<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Cli;

use Zend\Console\Request as ConsoleRequest;
use DateTime;
use DateInterval;

use FlamingBase\Controller\AbstractCliController;

use FlamingSms\Service\SmsService;
use FlamingSms\Entity\OutgoingSmsInterface;

/**
 * SmsQueueController
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class SmsQueueController extends AbstractCliController
{
	const QUEUE_CHUNK_SIZE = 100;

	/**
	 *
	 * @var SmsService
	 */
	protected $smsService;
	
	protected $emailReceiver;

	public function runAction()
	{
		$request = $this->getRequest();
		if (!$request instanceof ConsoleRequest)
			throw new \RuntimeException('You can only use this action from a console!');

		if (false === ($pid = $this->lock()))
			return;

		$sendMail = false;
		if ($request->getParam('m') || $request->getParam('send-mail'))
			$sendMail = true;

		$output = $this->runRegular(self::QUEUE_CHUNK_SIZE, $request->getParam('gatewayname'),
		                            $request->getParam('use-gateway'), $sendMail);

		$this->unlock();
		return $output;
	}

	protected function runRegular($limit, $gatewayName = null, $forceGateway = null, $sendMail = false)
	{
		//Record the start time
		$start = microtime(true);
		$smsCountTotal = 0;
		$smsSentCount = 0;
		$smsFailedCount = 0;
		$output = "Nothing to send\n";
		$outputRounds = array();
		// Run for 55 seconds
		for ($i = 0; $i < 11; $i++) {
			$startRun = microtime(true);
			$smses = $this->getSmsService()->findAllSendable($limit, $gatewayName);
			$smsCountTotal += count($smses);
			foreach ($smses as $sms) {
				if ($this->getSmsService()->send($sms, $forceGateway)) {
					$smsSentCount++;
					$outputRounds[] = "Sent SMS with ID: {$sms->getId()} - New status: {$sms->getStatus()} - Gateway: {$sms->getGatewayName()}";
				} else {
					$smsFailedCount++;
					$outputRounds[] = "ERROR! - Failed to send SMS with ID: {$sms->getId()} - New status: {$sms->getStatus()} - Gateway: {$sms->getGatewayName()}";
				}
			}
			// Sleep for 5 seconds - runtime, before checking for SMSes again
			$endRun = microtime(true);
			$sleepMicroTime = 5000000 - ((int)round(($endRun - $startRun) * 1000000));
			if(0 < $sleepMicroTime)
				usleep($sleepMicroTime);
		}

		//Record the end time
		$end = microtime(true);
		$runTime = round($end - $start, 4);
		if (0 < $smsCountTotal) {
			$output = "=== SENDING QUEUED MESSAGES ===\n";
			$output .= "QUEUE SIZE: $smsCountTotal - SUCCESFUL SENDS: $smsSentCount - FAILED SENDS: $smsFailedCount\n";
			$output .= "TOTAL RUN TIME: $runTime s\n";
			$output .= "=== MESSAGES ===\n";
			$output .= implode("\n", $outputRounds) . "\n";
		}


		if ($sendMail && 0 < $smsCountTotal) {
			$this->emailer()->sendMail($this->getEmailReceiver(),
			                           'FlamingSms [' . $this->env() . '] - smsqueue run',
			                           $output);
		}

		return $output;
	}

	public function cleanAction()
	{
		$request = $this->getRequest();
		if (!$request instanceof ConsoleRequest)
			throw new \RuntimeException('You can only use this action from a console!');

		if (false === ($pid = $this->lock()))
			return;

		$sendMail = false;
		if ($request->getParam('m') || $request->getParam('send-mail'))
			$sendMail = true;

		$output = $this->cleanSmsQueue($request->getParam('gatewayname'), $sendMail);

		$this->unlock();
		return $output;
	}

	protected function cleanSmsQueue($gatewayName = null, $sendMail = false)
	{
		$now = new DateTime('00:00:00');
		$aWeekAgo = new DateInterval('P1W');
		$timeoutDate = $now->sub($aWeekAgo);

		$smses = $this->getSmsService()->findByStatus(OutgoingSmsInterface::STATUS_QUEUED, null, $timeoutDate, $gatewayName);
		$queuedCleanupCount = count($smses);
		$output = "QUEUE TIMEOUT SIZE: $queuedCleanupCount\n";
		$output .= "=== CLEANING QUEUED MESSAGES OLDER THAN 1 WEEK ({$timeoutDate->format('Y-m-d H:i:s')}) ===\n\n";
		foreach ($smses as $sms) {
			$sms->setStatus(OutgoingSmsInterface::STATUS_NOT_DELIVERED);
			$output .= "Set status of SMS with ID: " . $sms->getId() . " - New status: " . $sms->getStatus() . " - Gateway: " . $sms->getGatewayName() . "\n";
		}

		$smses = $this->getSmsService()->findByStatus(OutgoingSmsInterface::STATUS_SENT, null, $timeoutDate, $gatewayName);
		$sentCleanupCount = count($smses);
		$output .= "\n\nSENT TIMEOUT SIZE: $sentCleanupCount\n";
		$output .= "=== CLEANING SENT MESSAGES OLDER THAN 1 WEEK ({$timeoutDate->format('Y-m-d H:i:s')}) ===\n\n";
		foreach ($smses as $sms) {
			$sms->setStatus(OutgoingSmsInterface::STATUS_TIMEOUT);
			$this->getSmsService()->updateOutgoingSmsInterface($sms);
			$output .= "Set status of SMS with ID: " . $sms->getId() . " - New status: " . $sms->getStatus() . " - Gateway: " . $sms->getGatewayName() . "\n";
		}

		if ((0 < $sentCleanupCount || 0 < $queuedCleanupCount) && $sendMail) {
			$this->emailer()->sendMail($this->getEmailReceiver(),
			                           'FlamingSms [' . $this->env() . '] - smsqueue clean',
			                           $output);
		}

		return $output;
	}

	/**
	 *
	 * @return SmsService
	 */
	public function getSmsService()
	{
		if (!$this->smsService) {
			$this->smsService = $this->getServiceLocator()->get('FlamingSms\Service\SmsService');
		}
		return $this->smsService;
	}

	/**
	 *
	 * @param SmsService $smsService
	 * @return SmsQueueController
	 */
	public function setSmsService(SmsService $smsService)
	{
		$this->smsService = $smsService;
		return $this;
	}
	
	public function getEmailReceiver()
	{
		if (!$this->emailReceiver) {
			$config = $this->getServiceLocator()->get('Configuration');
			$this->emailReceiver = $config['flamingsms']['sms_queue']['default_email_receiver'];
		}
		return $this->emailReceiver;
	}
}