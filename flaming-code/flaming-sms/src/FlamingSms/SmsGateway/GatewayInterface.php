<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\SmsGateway;

use FlamingSms\Entity\SmsInterface;

/**
 * GatewayInterface
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
interface GatewayInterface
{
	public function getName();
	public function setName($name);

	public function send(SmsInterface $sms);

	public function parseSendResponse($response);

	public function parseDeliveryReponse($response);
}