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
 * IncommingSms
 * 
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 * 
 * @ORM\Entity(repositoryClass="FlamingSms\Repository\IncommingSms")
 * @ORM\Table(name="incommingSmses")
 **/
class IncommingSms extends AbstractEntity implements IncommingSmsInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @var int
	 **/
	protected $id;

	/**
	 * @ORM\Column(type="string", length=10)
	 * @var string
	 **/
	protected $phone = '';

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 **/
	protected $content = '';

	/**
	 * @ORM\Column(type="string", length=10)
	 * @var string
	 **/
	protected $shortcode = '';

	/**
	 *
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 **/
	protected $receivedTime;

	public function getId()
	{
		return $this->id;
	}

	public function getPhone()
	{
		return $this->phone;
	}

	public function setPhone($phone)
	{
		$this->phone = (string) $phone;
		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setContent($content)
	{
		$this->content = (string) $content;
		return $this;
	}

	public function getShortcode()
	{
		return $this->shortcode;
	}

	public function setShortcode($shortcode)
	{
		$this->shortcode = (string) $shortcode;
		return $this;
	}

	public function getReceivedTime()
	{
		return $this->receivedTime;
	}

	public function setReceivedTime($receivedTime)
	{
		if ($receivedTime instanceof DateTime)
			$this->receivedTime = $receivedTime;
		else if (is_string($receivedTime))
			$this->receivedTime = new DateTime($receivedTime);
		return $this;
	}
}