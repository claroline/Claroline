<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\API\Finder;

use Claroline\CoreBundle\API\FinderInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Quiz questions finder (used by the Question bank).
 *
 * @DI\Service("ujm_exo.api.finder.question")
 * @DI\Tag("claroline.finder")
 */
class QuestionFinder implements FinderInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * QuestionFinder constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return 'UJM\ExoBundle\Entity\Item\Item';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
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
        if ($searches['selfOnly']) {
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
                    // search in title and content column
                    $qb
                        ->andWhere('(q.content LIKE :text OR q.title LIKE :contentText)')
                        ->setParameter('contentText', '%'.addcslashes($filterValue, '%_').'%');

                    break;
                default:
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
                    break;
            }
        }

        return $qb;
    }
}
