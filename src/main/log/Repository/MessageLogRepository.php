<?php

namespace Claroline\LogBundle\Repository;

use Claroline\LogBundle\Entity\MessageLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageLogRepository extends ServiceEntityRepository implements LogRepositoryInterface
{
    use LogRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageLog::class);
    }
}