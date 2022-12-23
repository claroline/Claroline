<?php

namespace Claroline\LogBundle\Repository;

use Claroline\LogBundle\Entity\SecurityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SecurityLogRepository extends ServiceEntityRepository implements LogRepositoryInterface
{
    use LogRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityLog::class);
    }
}