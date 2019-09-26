<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassFinder extends AbstractFinder
{
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return BadgeClass::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        $user = $this->tokenStorage->getToken()->getUser();

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'recipient':
                  $qb->join('obj.assertions', 'a');
                  $qb->join('a.recipient', 'r');
                  $qb->andWhere('r.uuid = :uuid');
                  $qb->setParameter('uuid', $filterValue);
                  break;
              case 'workspace':
                  $qb->join('obj.workspace', 'w');
                  $qb->andWhere('w.uuid = :workspace');
                  $qb->setParameter('workspace', $filterValue);
                  break;
              case 'assignable':
                  $qb->leftJoin('obj.allowedIssuers', 'user');
                  $qb->leftJoin('obj.allowedIssuersGroups', 'group');
                  $qb->leftJoin('group.users', 'groupUser');
                  $qb->leftJoin('obj.assertions', 'assertion');
                  $qb->leftJoin('assertion.recipient', 'recipient');

                  $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('user.id', $user->getId()),
                    $qb->expr()->eq('groupUser.id', $user->getId()),
                    $qb->expr()->orX(
                      $qb->expr()->andX(
                          $qb->expr()->eq('recipient.id', $user->getId()),
                          $qb->expr()->like('obj.issuingMode', '%'.BadgeClass::ISSUING_MODE_PEER.'%')
                      )
                    )
                  ));
                  //also from those who already have the badge

                  break;
              case 'meta.enabled':
                  $qb->andWhere('obj.enabled = :enabled');
                  $qb->setParameter('enabled', $filterValue);
                  break;
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
