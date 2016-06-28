<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\SmsGateway;

/**
 * AbstractGateway
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractGateway implements GatewayInterface
{
	/**
	 * A string identifying the gateway
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Returns the string identifying the gateway
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @param string $name
	 * @return GatewayInterface
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 *
	 * @param string $url
	 * @return GatewayInterface
	 */
	public function setUrl($url)
	{
		$this->url = (string) $url;
		return $this;
	}

	/**
	 *
	 * @param string $name A string identifying the gateway
	 * @param mixed $config An arbitrary config, e.g. could be an array
	 * @return void
	 */
	public function __construct($name = null, $config = null)
	{
		if (null !== $name)
			$this->setName($name);
		
		if (null !== $name)
			$this->configure($config);
	}
	
	/**
	 * It's up to the implementor to figure out how to configure the gateway
	 */
	abstract public function configure($config);
}