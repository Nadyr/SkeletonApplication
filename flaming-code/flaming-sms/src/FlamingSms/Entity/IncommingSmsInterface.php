<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Entity;

/**
 * IncommingSmsInterface
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
interface IncommingSmsInterface
{
	public function getId();

	public function getPhone();
	public function setPhone($phone);

	public function getContent();
	public function setContent($content);

	public function getShortCode();
	public function setShortCode($shortCode);

	public function getReceivedTime();
	public function setReceivedTime($receivedTime);
}