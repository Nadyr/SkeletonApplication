<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Entity;

use FlamingBase\Entity\AbstractEntity;

use Doctrine\ORM\Mapping as ORM;

use DateTime;

/**
 * OutgoingSms
 * 
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Entity(repositoryClass="FlamingSms\Repository\OutgoingSms")
 * @ORM\Table(name="outgoingSmses")
 **/
class OutgoingSms extends AbstractEntity implements OutgoingSmsInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @var int
	 **/
	protected $id;

	/**
	 * @ORM\Column(type="string", length=100)
	 * @var string
	 **/
	protected $gatewayName = '';

	/**
	 * @ORM\Column(type="string", length=100, unique=TRUE, nullable=TRUE)
	 * @var string
	 **/
	protected $gatewayId = null;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 **/
	protected $content = '';

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	protected $partCount = 1;

	/**
	 * @ORM\Column(type="string", length=10)
	 * @var string
	 **/
	protected $receiver = '';

	/**
	 * @ORM\Column(type="string", length=20)
	 * @var string
	 **/
	protected $senderId = 'SMS';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 **/
	protected $status = '';

	/**
	 *
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var DateTime
	 **/
	protected $sendTime;

	/**
	 *
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var DateTime
	 **/
	protected $sentTime;

	/**
	 *
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var DateTime
	 **/
	protected $deliveredTime;

	/**
	 * @ORM\OneToOne(targetEntity="OutgoingSms")
	 * @ORM\JoinColumn(name="parentSms_id", referencedColumnName="id")
	 * @var OutgoingSmsInterface
	 **/
	protected $parentSms;

	public function getId()
	{
		return $this->id;
	}

	public function getGatewayName()
	{
		return $this->gatewayName;
	}

	public function setGatewayName($name)
	{
		$this->gatewayName = (string) $name;
		return $this;
	}

	public function getGatewayId()
	{
		return $this->gatewayId;
	}

	public function setGatewayId($gatewayId)
	{
		$this->gatewayId = (string) $gatewayId;
		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setContent($content)
	{
		$this->content = (string) $content;
		$this->partCount = (int) ceil(mb_strlen($this->content, 'UTF-8') / self::SMS_LENGTH);
		return $this;
	}

	public function getPartCount()
	{
		return $this->partCount;
	}

	public function getReceiver()
	{
		return $this->receiver;
	}

	public function setReceiver($receiver)
	{
		$this->receiver = (string) $receiver;
		return $this;
	}

	public function getSenderId()
	{
		return $this->senderId;
	}

	public function setSenderId($senderId)
	{
		$this->senderId = (string) $senderId;
		return $this;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStatus($status)
	{
		$this->status = (string) $status;
		return $this;
	}

	public function getSendTime()
	{
		return $this->sendTime;
	}

	public function setSendTime($sendTime)
	{
		if ($sendTime instanceof DateTime)
			$this->sendTime = $sendTime;
		else if (is_string($sendTime))
			$this->sendTime = new DateTime($sendTime);
		return $this;
	}

	public function getSentTime()
	{
		return $this->sentTime;
	}

	public function setSentTime($sentTime)
	{
		if ($sentTime instanceof DateTime)
			$this->sentTime = $sentTime;
		else if (is_string($sentTime))
			$this->sentTime = new DateTime($sentTime);
		return $this;
	}

	public function getDeliveredTime()
	{
		return $this->deliveredTime;
	}

	public function setDeliveredTime($deliveredTime)
	{
		if ($deliveredTime instanceof DateTime)
			$this->deliveredTime = $deliveredTime;
		else if (is_string($deliveredTime))
			$this->deliveredTime = new DateTime($deliveredTime);
		return $this;
	}

	public function getParentSms()
	{
		return $this->parentSms;
	}

	public function setParentSms(OutgoingSmsInterface $parentSms)
	{
		$this->parentSms = $parentSms;
		return $this;
	}
}