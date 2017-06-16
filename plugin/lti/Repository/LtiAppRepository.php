<?php

namespace UJM\LtiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LtiAppRepository extends EntityRepository
{
    /**
     * @param int $appId
     * @param int $wsId
     *
     * Return queryBuilder
     */
    public function appAlreadyPublish($appId, $wsId)
    {
        $result = $this->createQueryBuilder('lti')
            ->join('lti.workspaces', 'w')
            ->where('w.id = :wid')
            ->andWhere('lti.id = :appid')
            ->setParameters(['wid' => $wsId, 'appid' => $appId])
            ->getQuery()->getResult();

        return $result;
    }

    /**
     * @param int $wsId
     *
     * Return queryBuilder
     */
    public function getAppsWs($wsId)
    {
        $result = $this->createQueryBuilder('lti')
            ->join('lti.workspaces', 'w')
            ->where('w.id = :wid')
            ->setParameter('wid', $wsId)
            ->getQuery()->getResult();

        return $result;
    }
}
