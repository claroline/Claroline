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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AssertionFinder extends AbstractFinder
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ToolMaskDecoderManager */
    private $toolMaskDecoderManager;

    public function __construct(TokenStorageInterface $tokenStorage, ToolMaskDecoderManager $toolMaskDecoderManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->toolMaskDecoderManager = $toolMaskDecoderManager;
    }

    public static function getClass(): string
    {
        return Assertion::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        $userJoin = false;
        $workspaceJoin = false;
        $badgeJoin = false;

        if (!array_key_exists('userDisabled', $searches) && !array_key_exists('user', $searches) && !array_key_exists('recipient', $searches)) {
            // don't show assertions of disabled/deleted users
            $qb->join('obj.recipient', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'badge':
                    if (!$badgeJoin) {
                        $qb->join('obj.badge', 'b');
                        $badgeJoin = true;
                    }
                    $qb->andWhere('b.uuid = :badge');
                    $qb->setParameter('badge', $filterValue);
                    break;
                case 'workspace':
                    if (!$workspaceJoin) {
                        if (!$badgeJoin) {
                            $qb->join('obj.badge', 'b');
                            $badgeJoin = true;
                        }
                        $qb->join('b.workspace', 'w');
                        $workspaceJoin = true;
                    }

                    $qb->andWhere('w.uuid = :workspace');
                    $qb->setParameter('workspace', $filterValue);
                    break;
                case 'user':
                case 'recipient':
                    if (!$userJoin) {
                        $qb->join('obj.recipient', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.uuid = :user');
                    $qb->setParameter('user', $filterValue);
                    break;
                case 'userDisabled':
                    if (is_bool($filterValue)) {
                        if (!$userJoin) {
                            $qb->join('obj.recipient', 'u');
                            $userJoin = true;
                        }
                        $qb->andWhere('u.isEnabled = :isEnabled');
                        $qb->andWhere('u.isRemoved = FALSE');
                        $qb->setParameter('isEnabled', !$filterValue);
                    }
                    break;
                case 'fromGrantableBadges':
                    $grantDecoder = $this->toolMaskDecoderManager->getMaskDecoderByToolAndName(
                        $this->om->getRepository(Tool::class)->findOneBy(['name' => 'badges']),
                        'grant'
                    );

                    if (!$badgeJoin) {
                        $qb->join('obj.badge', 'b');
                        $badgeJoin = true;
                    }

                    if (!$workspaceJoin) {
                        $qb->leftJoin('b.workspace', 'w');
                        $workspaceJoin = true;
                    }

                    if (!$userJoin) {
                        $qb->join('obj.recipient', 'u');
                        $userJoin = true;
                    }

                    $qb->leftJoin('b.issuer', 'o');

                    $subQb = $this->om->createQueryBuilder()
                        ->select('ot')
                        ->from('Claroline\CoreBundle\Entity\Tool\OrderedTool', 'ot')
                        ->leftJoin('ot.workspace', 'otw')
                        ->join('ot.rights', 'r')
                        ->join('r.role', 'rr')
                        ->where('ot.user IS NULL')
                        ->andWhere('((w.id IS NULL AND ot.workspace IS NULL) OR otw.id = w.id)')
                        ->andWhere('BIT_AND(r.mask, :grantMask) = :grantMask')
                        ->andWhere('rr.name IN (:userRoles)')
                        ->getDQL()
                    ;

                    $administratedOrganizations = $user->getAdministratedOrganizations()->toArray();

                    $qb->andWhere($qb->expr()->orX(
                        // always assignable by organization managers
                        !empty($administratedOrganizations) ? $qb->expr()->in('o.id', array_map(function (Organization $organization) {
                            return $organization->getId();
                        }, $administratedOrganizations)) : null,

                        // assignable by users with GRANT rights on the tool
                        $qb->expr()->exists($subQb),

                        // assignable by owners of the badge if enabled
                        $qb->expr()->andX(
                            $qb->expr()->eq('u.id', $user->getId()),
                            $qb->expr()->eq('b.issuingPeer', true)
                        )
                    ))
                        ->setParameter(':grantMask', $grantDecoder->getValue())
                        ->setParameter(':userRoles', $this->tokenStorage->getToken()->getRoleNames())
                    ;

                    break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.recipient', 'u');
                    }
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
