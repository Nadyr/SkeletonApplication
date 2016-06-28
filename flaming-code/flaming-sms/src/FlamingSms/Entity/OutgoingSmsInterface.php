<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Entity;

/**
 * OutgoingSmsInterface
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
interface OutgoingSmsInterface extends SmsInterface
{
	const SMS_LENGTH = 160;

	public function getId();

	public function getPartCount();

	public function getParentSms();
	public function setParentSms(OutgoingSmsInterface $parentSms);
}