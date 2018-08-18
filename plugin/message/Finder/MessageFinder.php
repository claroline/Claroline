<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\MessageBundle\Entity\Message;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.api.finder.messaging.message")
 * @DI\Tag("claroline.finder")
 */
class MessageFinder extends AbstractFinder
{
    /**
     * ParametersSerializer constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param SerializerProvider        $serializer
     * @param AbstractMessageSerializer $messageSerializer
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return Message::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.userMessages', 'um');
        $qb->leftJoin('um.user', 'currentUser');
        $userId = null;

        if ($this->tokenStorage && $this->tokenStorage->getToken() && 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $userId = $this->tokenStorage->getToken()->getUser()->getId();
            $qb->andWhere('currentUser.id = :userId');
            $qb->setParameter('userId', $userId);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'sent':
                    $qb->andWhere("um.isSent = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'removed':
                    $qb->andWhere("um.isRemoved = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'read':
                    $qb->andWhere("um.isRead = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'after':
                    $qb->andWhere("obj.date >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'before':
                    $qb->andWhere("obj.date <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'from':
                    $qb->andWhere("UPPER(obj.senderUsername) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'user':
                    $qb->leftJoin('um.user', 'user');
                    $qb->andWhere('user.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    public function getFilters()
    {
        return [
          'sent' => [
            'type' => 'boolean',
            'description' => 'The forum validation mode',
          ],
          'removed' => [
            'type' => 'boolean',
            'description' => 'The max amount of sub comments per messages',
          ],
          'read' => [
            'type' => 'boolean',
            'description' => 'The max amount of sub comments per messages',
          ],
          'after' => [
            'type' => 'date',
            'description' => 'The max amount of sub comments per messages',
          ],
          'before' => [
            'type' => 'date',
            'description' => 'The max amount of sub comments per messages',
          ],
          'from' => [
            'type' => 'string',
            'description' => 'The username ',
          ],
          'object' => [
            'type' => 'string',
            'description' => 'The message object',
          ],
          'content' => [
            'type' => 'string',
            'description' => 'The message content',
          ],
          'user' => [
            'type' => ['string'],
            'description' => 'The users uuid (default is current user)',
          ],
        ];
    }
}
