<?php

namespace Claroline\ProjectBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\ProjectBundle\Entity\Submission;
use Doctrine\ORM\QueryBuilder;

class SubmissionFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Submission::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], ?array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $userJoin = false;

        if (!array_key_exists('user', $searches)) {
            $qb->join('obj.user', 'u');
            $userJoin = true;

            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'project':
                    $qb->join('obj.project', 'p');
                    $qb->andWhere('p.uuid = :projectUuid');
                    $qb->setParameter('projectUuid', $filterValue);
                    break;

                case 'milestone':
                    $qb->join('obj.milestone', 'm');
                    $qb->andWhere('m.uuid = :milestoneUuid');
                    $qb->setParameter('milestoneUuid', $filterValue);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->andWhere("
                        UPPER(u.firstName) LIKE :name
                        OR UPPER(u.lastName) LIKE :name
                        OR UPPER(u.username) LIKE :name
                        OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :name
                        OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :name
                    ");
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
