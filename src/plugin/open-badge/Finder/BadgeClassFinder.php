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
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @deprecated
 */
class BadgeClassFinder extends AbstractFinder
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ToolMaskDecoderManager $toolMaskDecoderManager
    ) {
    }

    public static function getClass(): string
    {
        return BadgeClass::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        $workspaceJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    $qb->join('obj.issuer', 'o');
                    $qb->andWhere('o.uuid IN (:organizations)');
                    $qb->setParameter('organizations', $filterValue);
                    break;

                case 'recipient':
                    $qb->join('obj.assertions', 'a');
                    $qb->join('a.recipient', 'r');
                    $qb->andWhere('r.uuid = :uuid');
                    $qb->setParameter('uuid', $filterValue);
                    break;

                case 'workspace':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w');
                        $workspaceJoin = true;
                    }

                    $qb->andWhere('w.uuid = :workspace');
                    $qb->setParameter('workspace', $filterValue);
                    break;

                case 'assignable':
                    if (!in_array('ROLE_ADMIN', $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS])) {
                        $grantDecoder = $this->toolMaskDecoderManager->getMaskDecoderByToolAndName(
                            'badges',
                            'grant'
                        );

                        $qb->leftJoin('obj.issuer', 'o');
                        if (!$workspaceJoin) {
                            $qb->leftJoin('obj.workspace', 'w');
                            $workspaceJoin = true;
                        }

                        $qb->leftJoin('obj.assertions', 'assertion');
                        $qb->leftJoin('assertion.recipient', 'recipient');

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

                        $qb->andWhere($qb->expr()->orX(
                            // always assignable by organization managers
                            $qb->expr()->in('o.id', array_map(function (Organization $organization) {
                                return $organization->getId();
                            }, $user->getAdministratedOrganizations())),

                            // assignable by users with GRANT rights on the tool
                            $qb->expr()->exists($subQb),

                            // assignable by owners of the badge if enabled
                            $qb->expr()->andX(
                                $qb->expr()->eq('recipient.id', $user->getId()),
                                $qb->expr()->eq('obj.issuingPeer', true)
                            )
                        ))
                            ->setParameter(':grantMask', $grantDecoder->getValue())
                            ->setParameter(':userRoles', $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS])
                        ;
                    }

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
