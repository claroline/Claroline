<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Quiz questions finder (used by the Question bank).
 */
class QuestionFinder extends AbstractFinder
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * QuestionFinder constructor.
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getClass(): string
    {
        return 'UJM\ExoBundle\Entity\Item\Item';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        // only search in questions (not content items)
        // in any case exclude every mimeType that does not begin with [application] from results
        if (!empty($searches['mimeType'])) {
            $qb
                ->andWhere('obj.mimeType = :mimeType')
                ->setParameter('mimeType', $searches['mimeType']);
            // remove from filters
            unset($searches['mimeType']);
        } else {
            $qb
                ->andWhere('obj.mimeType LIKE :questionPrefix')
                ->setParameter('questionPrefix', 'application%');
        }

        // get questions visible by the current user
        if (isset($searches['selfOnly'])) {
            // only get questions created by the User
            $qb->andWhere('obj.creator = :user');

            // remove from filters
            unset($searches['selfOnly']);
        } else {
            // includes shared questions
            $qb->leftJoin('UJM\ExoBundle\Entity\Item\Shared', 's', Join::WITH, 'obj = s.question');
            $qb->andWhere('(obj.creator = :user OR s.user = :user)');
        }

        $qb->setParameter('user', $this->tokenStorage->getToken()->getUser());

        // process other filters
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'content':
                    $searchString = addcslashes($filterValue, '%_');
                    // search in title and content column
                    $qb
                        ->andWhere('obj.content LIKE :content OR obj.title LIKE :content')
                        ->setParameter($filterName, '%'.$searchString.'%');

                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
