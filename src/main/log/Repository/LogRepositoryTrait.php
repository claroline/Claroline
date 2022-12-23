<?php

namespace Claroline\LogBundle\Repository;

use Claroline\LogBundle\Entity\AbstractLog;
use Doctrine\Common\Collections\Collection;

trait LogRepositoryTrait
{
    public function findLogsOlderThan(\DateTimeInterface $date): Collection
    {
        $qb = $this->createQueryBuilder('log');
        $qb
            ->where('log.date < :from')
            ->orderBy('date')
            ->setParameter(':from', $date);

        /** @var Collection|AbstractLog[] $logs */
        $logs = $qb->getQuery()->getResult();

        return $logs;
    }
}