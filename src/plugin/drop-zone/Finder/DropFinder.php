<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\DropZoneBundle\Entity\Drop;
use Doctrine\ORM\QueryBuilder;

class DropFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Drop::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $userJoin = false;
        if (!array_key_exists('user', $searches)) {
            $qb->join('obj.user', 'u');
            $userJoin = true;

            // automatically excludes results for disabled/deleted users
            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'dropzone':
                    $qb->join('obj.dropzone', 'd');
                    $qb->andWhere('d.uuid = :dropzoneUuid');
                    $qb->setParameter('dropzoneUuid', $searches['dropzone']);
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
