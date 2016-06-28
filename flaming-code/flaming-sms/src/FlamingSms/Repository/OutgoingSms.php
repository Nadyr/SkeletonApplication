<?php

/*
 * Copyright (c) 2013, Flaming Code
 * 
 */

namespace FlamingSms\Repository;

use Doctrine\ORM\EntityRepository;

use DateTime;
use DateInterval;

/**
 * OutgoingSms
 *
 * @author Flemming Andersen <flemming@flamingcode.com>
 * @copyright (c) 2013, Flaming Code
 * @link http://github.com/FlamingCode/FlamingSms for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT
 */
class OutgoingSms extends EntityRepository
{
	public function countSent()
	{
		$queryBuilder = $this->getCountSentQueryBuilder();

		$query = $queryBuilder->getQuery();

		return (int) $query->getSingleScalarResult();
	}

	public function countSentLastMonth()
	{
		$monthStart = new DateTime(date('Y-m-01'));
		$monthStart->sub(new DateInterval('P1M'));
		$monthEnd = clone $monthStart;
		$monthEnd->add(new DateInterval('P1M'));

		$queryBuilder = $this->getCountSentQueryBuilder();
		$queryBuilder->andWhere('sms.sentTime >= :startDate')
		             ->andWhere('sms.sentTime < :endDate')
		             ->setParameter('startDate', $monthStart->format('Y-m-d H:i:s'))
		             ->setParameter('endDate', $monthEnd->format('Y-m-d H:i:s'));

		$query = $queryBuilder->getQuery();

		return (int) $query->getSingleScalarResult();
	}

	public function countSentToday()
	{
		$now = new DateTime;
		$now->setTime(0, 0, 0);

		$dayStart = clone $now;
		$dayEnd = clone $now;
		unset($now);

		$dayEnd->add(new DateInterval('P1D'));

		$queryBuilder = $this->getCountSentQueryBuilder();
		$queryBuilder->andWhere('sms.sentTime >= :startDate')
		             ->andWhere('sms.sentTime < :endDate')
		             ->setParameter('startDate', $dayStart->format('Y-m-d H:i:s'))
		             ->setParameter('endDate', $dayEnd->format('Y-m-d H:i:s'));

		$query = $queryBuilder->getQuery();

		return (int) $query->getSingleScalarResult();
	}

	/**
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getCountSentQueryBuilder()
	{
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder->select('SUM(sms.partCount)')
		             ->from('Tasti\Entity\OutgoingSms', 'sms')
		             ->where($queryBuilder->expr()->orX()->addMultiple(array(
				     'sms.status = :sentStatus',
				     'sms.status = :deliveredTHStatus',
				     'sms.status = :deliveredTNStatus'
			     )))
		             ->setParameter('sentStatus', OutgoingSmsEntity::STATUS_SENT)
		             ->setParameter('deliveredTHStatus', OutgoingSmsEntity::STATUS_DELIVERED_TO_HANDSET)
		             ->setParameter('deliveredTNStatus', OutgoingSmsEntity::STATUS_DELIVERED_TO_NETWORK);

		return $queryBuilder;
	}
}