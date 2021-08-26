<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Quota;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class QuotaFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getClass(): string
    {
        return Quota::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                $qb->join('obj.organization', 'o');
                $qb->join('o.administrators', 'a');
                $qb->andWhere('a.id = :userId');
                $qb->setParameter('userId', $currentUser->getId());
            }
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
