<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Entity;

/**
 * SmsInterface
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
interface SmsInterface
{
	const STATUS_SENT                 = 'SENT'; // Sent to gateway
	const STATUS_QUEUED               = 'QUEUED'; // Queued in our system
	const STATUS_DELIVERED_TO_HANDSET = 'DELIVERED_TO_HANDSET'; // Delivered to receiver
	const STATUS_DELIVERED_TO_NETWORK = 'DELIVERED_TO_NETWORK'; // Delivered to receiver
	const STATUS_NOT_DELIVERED        = 'NOT_DELIVERED'; // Could not be delivered to receiver
	const STATUS_TIMEOUT              = 'TIMEOUT'; // Sent but no status has been given from the gateway for 7 days
	const STATUS_BLOCKED              = 'BLOCKED'; // Receiver has blocked or opted out of receiving SMSes from this gateway
	const STATUS_INVALID_RECIPIENT    = 'INVALID_RECIPIENT';

	public function getGatewayName();
	public function setGatewayName($name);

	public function getGatewayId();
	public function setGatewayId($id);

	public function getContent();
	public function setContent($content);

	public function getReceiver();
	public function setReceiver($receiver);

	public function getSenderId();
	public function setSenderId($id);

	public function getStatus();
	public function setStatus($status);

	public function getSendTime();
	public function setSendTime($sendTime);

	public function getSentTime();
	public function setSentTime($sentTime);

	public function getDeliveredTime();
	public function setDeliveredTime($deliveredTime);
}