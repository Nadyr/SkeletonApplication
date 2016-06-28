<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use FlamingSms\Service\SmsService;

/**
 * NotificationController
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class NotificationController extends AbstractActionController
{
	/**
	 *
	 * @var SmsService
	 */
	protected $smsService;

	public function coolsmsAction()
	{
		$request = $this->getRequest();

		$response = $this->getResponse();
		$response->setContent('');

		$headers = $response->getHeaders();
		$headers->addHeaderLine('Content-Type', 'text/plain');

		$data = $request->getQuery();
		$status = isset($data['status']) ? $data['status'] : null;
		$smsId = isset($data['msgid']) ? $data['msgid'] : null;
		$statuscode = isset($data['statuscode']) ? $data['statuscode'] : null;

		if(null != $status && null != $smsId) {
			if (null != $statuscode)
				$status .= '-' . $statuscode;

			if ($sms = $this->getSmsService()->findOutgoingSmsByGatewayId($smsId)) {
				$this->getSmsService()->handleStatusNotification($sms, $status);

				$response->setStatusCode(200);
				$response->setContent('OK');
			} else {
				$response->setStatusCode(404);
			}
		} else {
			$response->setStatusCode(400);
		}

		return $response;
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
	 * @return IncommingController
	 */
	public function setSmsService(SmsService $smsService)
	{
		$this->smsService = $smsService;
		return $this;
	}
}
