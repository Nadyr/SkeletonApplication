<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Service;

use DateTime;

use Zend\ServiceManager\AbstractFactoryInterface;

use FlamingBase\Service\AbstractService;

use FlamingSms\Entity\OutgoingSmsInterface;
use FlamingSms\Entity\IncommingSmsInterface;
use FlamingSms\SmsGateway\GatewayInterface;

/**
 * SmsService
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class SmsService extends AbstractService
{
	/**
	 *
	 * @var AbstractFactoryInterface 
	 */
	protected $gatewayFactory;

	/**
	 *
	 * @var array
	 */
	protected $gateways = array();

	/**
	 *
	 * @var string
	 */
	protected $defaultGatewayName;
	
	public function findOutgoingSmsById($id)
	{
		return $this->getEntityManager()->find($this->getOption('outgoing_sms_entity'), $id);
	}

	public function findOutgoingSmsByGatewayId($id)
	{
		return $this->getEntityManager()->getRepository($this->getOption('outgoing_sms_entity'))
		                                ->findOneBy(array('gatewayId' => $id));
	}

	public function handleStatusNotification(OutgoingSmsInterface $sms, $status)
	{
		$gw = $this->getGateway($sms->getGatewayName());
		if (null !== ($newStatus = $gw->parseDeliveryReponse($status))) {
			$sms->setStatus($newStatus);
			if (OutgoingSmsInterface::STATUS_DELIVERED_TO_HANDSET ||
			    OutgoingSmsInterface::STATUS_DELIVERED_TO_NETWORK) {
				$sms->setDeliveredTime(new DateTime);
			}

			$this->updateOutgoingSms($sms);
		}
		return $sms;
	}

	public function findAllOutgoingSmses()
	{
		return $this->getEntityManager()->getRepository($this->getOption('outgoing_sms_entity'))->findAll();
	}

	public function findByStatus($status, $limit = null, DateTime $before = null, $gatewayName = null)
	{
		$em = $this->getEntityManager();
		$queryBuilder = $em->createQueryBuilder();
		$queryBuilder->select(array('s'))
		                      ->from($this->getOption('outgoing_sms_entity'), 's')
		                      ->where('s.status = :status')
		                      ->orderBy('s.id', 'ASC')
		                      ->setParameter('status', $status);

		if ($before) {
			$queryBuilder->andWhere('s.sentTime < :before')
			             ->setParameter('before', $before->format('Y-m-d H:i:s'));
		}

		if (null !== $gatewayName && GatewayFactory::hasGateway($gatewayName)) {
			$queryBuilder->andWhere('s.gatewayName = :gwname')
			             ->setParameter('gwname', $gatewayName);
		}

		if ($limit)
			$queryBuilder->setMaxResults($limit);

		$query = $queryBuilder->getQuery();

		return $query->getResult();
	}

	public function findAllSendable($limit, $gatewayName = null)
	{
		$now = new DateTime();

		$em = $this->getEntityManager();
		$queryBuilder = $em->createQueryBuilder();
		$queryBuilder->select(array('s'))
		                      ->from($this->getOption('outgoing_sms_entity'), 's')
		                      ->where('s.status = :status')
		                      ->andWhere($queryBuilder->expr()->orX(
			$queryBuilder->expr()->isNull('s.sendTime'),
			$queryBuilder->expr()->lte('s.sendTime', ':now')
		))
		                      ->orderBy('s.id', 'ASC')
		                      ->setParameter('now', $now->format('Y-m-d H:i') . ':00')
		                      ->setParameter('status', OutgoingSmsInterface::STATUS_QUEUED)
		                      ->setMaxResults($limit);

		if (null !== $gatewayName && GatewayFactory::hasGateway($gatewayName)) {
			$queryBuilder->andWhere('s.gatewayName = :gwname')
			             ->setParameter('gwname', $gatewayName);
		}

		$query = $queryBuilder->getQuery();

		return $query->getResult();
	}

	public function countSent()
	{
		return $this->getEntityManager()->getRepository($this->getOption('outgoing_sms_entity'))
		                                ->countSent();
	}

	public function countSentToday()
	{
		return $this->getEntityManager()->getRepository($this->getOption('outgoing_sms_entity'))
		                                ->countSentToday();
	}

	public function countSentLastMonth()
	{
		return $this->getEntityManager()->getRepository($this->getOption('outgoing_sms_entity'))
		                                ->countSentLastMonth();
	}

	public function send(OutgoingSmsInterface $sms, $forceGatewayName = null)
	{
		if (null !== $forceGatewayName) {
			$gw = $this->getGateway($forceGatewayName);
			$sms->setGatewayName($gw->getName());
			$sending = $gw->send($sms);
		} else
			$sending = $this->getGateway($sms->getGatewayName())->send($sms);

		if ($sending) {
			$sms->setSentTime(new DateTime);
			$this->updateOutgoingSms($sms);
		}

		return $sending;
	}

	public function findIncommingSmsById($id)
	{
		return $this->getEntityManager()->find($this->getOption('incomming_sms_entity'), $id);
	}

	public function findAllIncommingSmses()
	{
		return $this->getEntityManager()->getRepository($this->getOption('incomming_sms_entity'))->findAll();
	}

	public function createOutgoingSms($sms)
	{
		if (is_array($sms)) {
			$class = $this->getOption('outgoing_sms_entity');
			$sms = $this->getHydrator()->hydrate($sms, new $class);
		}

		$this->getEntityManager()->persist($sms);
		$this->getEntityManager()->flush();

		return $sms;
	}

	public function updateOutgoingSms(OutgoingSmsInterface $sms)
	{
		$this->getEntityManager()->persist($sms);
		$this->getEntityManager()->flush();

		return $sms;
	}

	public function deleteOutgoingSms(OutgoingSmsInterface $sms)
	{
		$this->getEntityManager()->remove($sms);
		$this->getEntityManager()->flush();

		// TODO: What should we return?
		return;
	}

	public function createIncommingSms($sms)
	{
		if (is_array($sms)) {
			$class = $this->getOption('incomming_sms_entity');
			$sms = $this->getHydrator()->hydrate($sms, new $class);
		}

		$sms->setReceivedTime(new DateTime);

		$this->getEntityManager()->persist($sms);
		$this->getEntityManager()->flush();

		return $sms;
	}

	public function updateIncommingSms(IncommingSmsInterface $sms)
	{
		$this->getEntityManager()->persist($sms);
		$this->getEntityManager()->flush();

		return $sms;
	}

	public function deleteIncommingSms(IncommingSmsInterface $sms)
	{
		$this->getEntityManager()->remove($sms);
		$this->getEntityManager()->flush();

		// TODO: What should we return?
		return;
	}
	
	public function getDefaultGatewayName()
	{
		return $this->defaultGatewayName;
	}

	public function setDefaultGatewayName($name)
	{
		if ($this->getGatewayFactory()->hasGateway($name))
			$this->defaultGatewayName = $name;
		return $this;
	}
	
	public function getGatewayFactory()
	{
		return $this->gatewayFactory;
	}
	
	public function setGatewayFactory(AbstractFactoryInterface $factory)
	{
		$this->gatewayFactory = $factory;
		return $this;
	}
	
	/**
	 *
	 * @return GatewayInterface
	 */
	public function getGateway($name = null)
	{
		if (null === $name)
			$name = $this->getDefaultGatewayName();

		if (array_key_exists($name, $this->gateways))
			return $this->gateways[$name];
		else if ($this->getGatewayFactory()->hasGateway($name)) {
			$this->gateways[$name] = $this->getGatewayFactory()->createGateway($name);
			return $this->gateways[$name];
		}

		return null;
	}
}