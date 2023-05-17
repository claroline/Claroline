<?php

namespace Claroline\CommunityBundle\Finder\Filter;

use Claroline\AppBundle\API\Finder\FinderFilterInterface;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserFilter implements FinderFilterInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function addFilter(QueryBuilder $qb, string $alias, ?array $searches = []): QueryBuilder
    {
        // if we don't explicitly request for it, we will not return disabled or removed users
        if (!in_array('disabled', array_keys($searches)) || !$searches['disabled']) {
            $qb->andWhere("({$alias}.id IS NULL OR ({$alias}.isEnabled = TRUE AND {$alias}.isRemoved = FALSE))");
        } else {
            $qb->andWhere("{$alias}.isEnabled = FALSE");
            $qb->andWhere("{$alias}.isRemoved = FALSE");
        }

        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!$currentUser instanceof User || !$currentUser->isTechnical()) {
            $qb->andWhere("({$alias}.id IS NULL OR {$alias}.technical = false)");
        }

        return $qb;
    }
}
