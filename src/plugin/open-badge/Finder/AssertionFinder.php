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
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AssertionFinder extends AbstractFinder
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ToolMaskDecoderManager $toolMaskDecoderManager
    ) {
    }

    public static function getClass(): string
    {
        return Assertion::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $userJoin = false;
        $workspaceJoin = false;
        $badgeJoin = false;

        if (!array_key_exists('user', $searches) && !array_key_exists('recipient', $searches)) {
            $qb->join('obj.recipient', 'u');
            $userJoin = true;

            // automatically excludes results for disabled/deleted users
            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()?->getUser();

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
                case 'fromGrantableBadges':
                    $grantDecoder = $this->toolMaskDecoderManager->getMaskDecoderByToolAndName(
                        'badges',
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
                        ->join('ot.rights', 'r')
                        ->join('r.role', 'rr')
                        ->where('(ot.contextId IS NULL OR ot.contextId = w.uuid)')
                        ->andWhere('BIT_AND(r.mask, :grantMask) = :grantMask')
                        ->andWhere('rr.name IN (:userRoles)')
                        ->getDQL()
                    ;

                    $administratedOrganizations = $user->getAdministratedOrganizations();

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
                        ->setParameter(':userRoles', $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS])
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
