<?php

namespace Claroline\AppBundle\Repository;

use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\AppBundle\Entity\UserViewCounterInterface;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

trait ViewerRepositoryTrait
{
    abstract protected static function getSubjectProp(): string;

    abstract public function getClassName(): string;

    public function countVisitors(UserViewCounterInterface $subject, Organization $organization): int
    {
        $subjectProp = static::getSubjectProp();

        return (int) $this->getEntityManager()
            ->createQuery("
                SELECT COUNT(DISTINCT v.user)
                FROM {$this->getClassName()} AS v
                LEFT JOIN v.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE v.$subjectProp = :subject
                  AND uo.organization = :organization
            ")
            ->setParameter('subject', $subject)
            ->setParameter('organization', $organization)
            ->getSingleScalarResult();
    }

    public function findOneByDateAndUser(UserViewCounterInterface $subject, \DateTimeInterface $date, ?User $user = null): ?AbstractUserView
    {
        $subjectProp = static::getSubjectProp();

        return $this->createQueryBuilder('v')
            ->where('v.seenAt LIKE :date')
            ->andWhere("v.$subjectProp = :subject")
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d').'%')
            ->setParameter('subject', $subject)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findViewsForPeriod(UserViewCounterInterface $subject, Organization $organization): array
    {
        $now = new \DateTime();
        $start = $now->sub(new \DateInterval('P52W'));

        $subjectProp = static::getSubjectProp();

        $results = $this->getEntityManager()
            ->createQuery("
                SELECT YEAR(v.seenAt) AS y, MONTH(v.seenAt) AS m, DAY(v.seenAt) AS d, SUM(v.count) AS count
                FROM {$this->getClassName()} AS v
                LEFT JOIN v.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE v.$subjectProp = :subject
                  AND uo.organization = :organization
                  AND v.seenAt >= :start 
                GROUP BY y, m, d
            ")
            ->setParameter('subject', $subject)
            ->setParameter('organization', $organization)
            ->setParameter('start', $start)
            ->getArrayResult();

        $activity = [];
        foreach ($results as $result) {
            $activity[$result['y'].'-'.$result['m'].'-'.$result['d']] = (int) $result['count'];
        }

        return $activity;
    }

    public function findVisitorsForPeriod(UserViewCounterInterface $subject, Organization $organization): array
    {
        $now = new \DateTime();
        $start = $now->sub(new \DateInterval('P52W'));

        $subjectProp = static::getSubjectProp();

        $results = $this->getEntityManager()
            ->createQuery("
                SELECT YEAR(v.seenAt) AS y, MONTH(v.seenAt) AS m, DAY(v.seenAt) AS d, COUNT(DISTINCT v.user) AS count
                FROM {$this->getClassName()} AS v
                LEFT JOIN v.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE v.$subjectProp = :subject
                  AND uo.organization = :organization
                  AND v.seenAt >= :start 
                GROUP BY y, m, d
            ")
            ->setParameter('subject', $subject)
            ->setParameter('organization', $organization)
            ->setParameter('start', $start)
            ->getArrayResult();

        $activity = [];
        foreach ($results as $result) {
            $activity[$result['y'].'-'.$result['m'].'-'.$result['d']] = (int) $result['count'];
        }

        return $activity;
    }
}
