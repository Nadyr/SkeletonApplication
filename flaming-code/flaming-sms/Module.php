<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

/**
 * Module
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class Module implements AutoloaderProviderInterface, ConsoleUsageProviderInterface
{
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'FlamingSms\SmsGateway\Factory' => function($sm) {
					$config = $sm->get('Configuration');
					$config = $config['flamingsms'];
					return new SmsGateway\Factory($config['gateway_classmap'], $config['gateway_config']);
				},
				
				'FlamingSms\Service\SmsService' => function($sm) {
					$config = $sm->get('Configuration');
					$config = $config['flamingsms'];
					$service = new Service\SmsService;
					$service->setEntityManager($sm->get('Doctrine\ORM\EntityManager'))
					        ->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods(false))
					        ->setOptions($config['sms_service'])
					        ->setGatewayFactory($sm->get('FlamingSms\SmsGateway\Factory'))
					        ->setDefaultGatewayName($config['default_gateway_name']);
					return $service;
				},
			)
		);
	}
	
	public function getControllerPluginConfig()
	{
		return array(
			'factories' => array(
				'smsSender' => function($helpers) {
					$serviceLocator = $helpers->getServiceLocator();
					$smsService = $serviceLocator->get('FlamingSms\Service\SmsService');
					return new Controller\Plugin\SmsSender($smsService);
				},
			),
		);
	}

	public function onBootstrap($e)
	{
		/* @var $application \Zend\Mvc\Application */
		$application = $e->getApplication();
		/* @var $eventManager \Zend\EventManager\EventManager */
		$eventManager = $application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);

		/* @var $serviceManager \Zend\ServiceManager\ServiceManager */
		$serviceManager = $application->getServiceManager();
	}

	public function getConsoleUsage(Console $console)
	{
		return array(
			'SMS Queue Management',
			'smsqueue run [-m|--send-mail] [--use-gateway=] [gateway]' => 'Run the sms queue. Optionally specify the gateway',
			'smsqueue clean [-m|--send-mail] [gateway]' => 'Clean the sms queue. Optionally specify the gateway',
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				)
			)
		);
	}
}
