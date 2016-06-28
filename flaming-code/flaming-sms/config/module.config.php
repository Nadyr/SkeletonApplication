<?php
namespace FlamingSms;

return array(
	'controllers' => array(
		'invokables' => array(
			'FlamingSms\Controller\Incomming' => 'FlamingSms\Controller\IncommingController',
			'FlamingSms\Controller\Notification' => 'FlamingSms\Controller\NotificationController',
			
			// Console controllers
			'FlamingSms\Cli\SmsQueue' => 'FlamingSms\Cli\SmsQueueController',
		)
	),
	
	'router' => array(
		'routes' => array(
			'flamingsms' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/sms',
					'defaults' => array(
						'__NAMESPACE__' => 'FlamingSms\Controller',
						'controller' => 'Index',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'incomming' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/incomming[/:action]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'controller' => 'Incomming',
								'action' => 'index'
							)
						)
					),

					'notification' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/notification[/:action]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'controller' => 'Notification',
								'action' => 'index'
							)
						)
					),
				)
			)
		)
	),
	
	'console' => array(
		'router' => array(
			'routes' => array(
				'smsqueue-run' => array(
					'options' => array(
						'route' => 'smsqueue run [-m|--send-mail] [--use-gateway=] [<gatewayname>]',
						'defaults' => array(
							'controller' => 'FlamingSms\Cli\Smsqueue',
							'action' => 'run'
						)
					)
				),

				'smsqueue-clean' => array(
					'options' => array(
						'route' => 'smsqueue clean [-m|--send-mail] [<gatewayname>]',
						'defaults' => array(
							'controller' => 'FlamingSms\Cli\SmsQueue',
							'action' => 'clean'
						)
					)
				),
			)
		)
	),
	
	// Doctrine config
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	
	'flamingsms' => array(
		'sms_service' => array(
			'outgoing_sms_entity' => 'FlamingSms\Entity\OutgoingSms',
			'incomming_sms_entity' => 'FlamingSms\Entity\IncommingSms',
		),
		
		'default_gateway_name' => 'coolsms',
		
		// Key-value pairs of gateway names and gateway classes
		'gateway_classmap' => array(
			'coolsms' => 'FlamingSms\SmsGateway\CoolSms',
			'fakegw' => 'FlamingSms\SmsGateway\FakeGw',
		),
		
		'sms_queue' => array(
			'default_email_receiver' => 'my-receiver@email.invalid'
		),

		'gateway_config' => array(
			'coolsms' => array(
				'url' => 'https://sms.coolsmsc.dk',
				
				// These should be set in a *.local.php config file
				'username' => 'myusername',
				'password' => 'mypassword',
				'notification_url' => 'http://my-url.invalid/notification/coolsms'
			),
			
			// For testing purposes you can make your own "gateway" by just saving the messages to a DB
			'fakegw' => array(
				'url' => '',
				'username' => '',
				'password' => '',
				'notification_url' => 'http://my-url.invalid/notification/gwexample'
			),
		)
	),
	
//	'error_handler' => array(
//		'send_mail' => false,
//		'mail_options' => array(
//			'to' => 'some-receiver@domain.invalid',
//			'subject' => 'Exception mail'
//		)
//	),
);