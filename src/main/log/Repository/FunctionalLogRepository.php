<?php

namespace Claroline\LogBundle\Repository;

use Claroline\LogBundle\Entity\FunctionalLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FunctionalLogRepository extends ServiceEntityRepository implements LogRepositoryInterface
{
    use LogRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FunctionalLog::class);
    }
}