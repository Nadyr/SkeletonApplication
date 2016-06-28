<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\SmsGateway;

use Zend\ServiceManager\AbstractFactoryInterface;

/**
 * Factory
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class Factory implements AbstractFactoryInterface
{
	/**
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 *
	 * @var array
	 */
	protected $classmap = array();
	
	public function canCreateServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		return $this->hasGateway($name);
	}
	
	public function createServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		return $this->createGateway($name);
	}

	public function hasGateway($name)
	{
		return array_key_exists($name, $this->classmap);
	}
	
	public function setClassmap(array $classmap)
	{
		$this->classmap = $classmap;
		return $this;
	}
	
	public function setConfig(array $config)
	{
		$this->config = $config;
		return $this;
	}

	public function createGateway($name)
	{
		if ($this->hasGateway($name))
			return new $this->classmap[$name]($name, $this->config[$name]);
		else
			return null;
	}

	public function __construct(array $classmap = null, array $config = null)
	{
		if (null !== $classmap)
			$this->setClassmap($classmap);
		if (null !== $config)
			$this->setConfig($config);
	}
}