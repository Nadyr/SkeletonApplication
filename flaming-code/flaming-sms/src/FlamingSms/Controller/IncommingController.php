<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use FlamingSms\Service\SmsService;

/**
 * IncommingController
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class IncommingController extends AbstractActionController
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

		if (!$request->isPost()) {
			$response->setStatusCode(404);
			return $response;
		}

		$data = $request->getPost();

		$sms = array(
			'shortcode' => $data['NUMBER'],
			'phone' => $data['FROM'],
			'content' => mb_strtoupper(trim($data['SMS']), 'UTF-8')
		);

		$sms = $this->getSmsService()->createIncommingSms($sms);
		
		//TODO: Here we should fire off an event

		$response->setStatusCode(200);
		$response->setContent('OK');

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