<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Icap\NotificationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use Icap\NotificationBundle\Entity\NotificationViewer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.contact")
 * @DI\Tag("claroline.finder")
 */
class NotificationViewerFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function getClass()
    {
        return NotificationViewer::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.notification', 'notification');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'user':
                $qb->andWhere('obj.viewerId = :viewerId');
                $qb->setParameter('viewerId', $filterValue);
                break;
              case 'types':
                foreach ($filterValue as $name) {
                    $qb->andWhere(
                      $qb->expr()
                        ->notLike(
                          'notification.actionKey',
                          $qb->expr()->literal('%'.$name.'%')
                      )
                    );
                    break;
                }
                break;
              case 'category':
                if ('system' !== $category) {
                    $qb->andWhere('notification.iconKey = :category')
                        ->setParameter('category', $category);
                } else {
                    $qb->andWhere(
                      $qb->expr()->isNull('notification.iconKey')
                    );
                }
                break;
          }
        }

        $this->sortBy($qb, $sortBy);

        return $qb;
    }

    //probably deprecated since we try hard to optimize everything and is a duplicata of getExtraFieldMapping
    private function sortBy($qb, array $sortBy = null)
    {
        // manages custom sort properties
        if ($sortBy && 0 !== $sortBy['direction']) {
            switch ($sortBy['property']) {
              case 'name':
                  $qb->orderBy('obj.lastName', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                  break;
              case 'isDisabled':
                  $qb->orderBy('obj.isEnabled', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                  break;
          }
        }

        return $qb;
    }
}
