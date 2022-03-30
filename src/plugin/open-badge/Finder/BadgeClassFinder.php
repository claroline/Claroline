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
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassFinder extends AbstractFinder
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
        return BadgeClass::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

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
                    if (!in_array('ROLE_ADMIN', $this->tokenStorage->getToken()->getRoleNames())) {
                        $grantDecoder = $this->toolMaskDecoderManager->getMaskDecoderByToolAndName(
                            $this->om->getRepository(Tool::class)->findOneBy(['name' => 'badges']),
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
                            ->leftJoin('ot.workspace', 'otw')
                            ->join('ot.rights', 'r')
                            ->join('r.role', 'rr')
                            ->where('ot.user IS NULL')
                            ->andWhere('((w.id IS NULL AND ot.workspace IS NULL) OR otw.id = w.id)')
                            ->andWhere('BIT_AND(r.mask, :grantMask) = :grantMask')
                            ->andWhere('rr.name IN (:userRoles)')
                            ->getDQL()
                        ;

                        $qb->andWhere($qb->expr()->orX(
                            // always assignable by organization managers
                            $qb->expr()->in('o.id', array_map(function (Organization $organization) {
                                return $organization->getId();
                            }, $user->getAdministratedOrganizations()->toArray())),

                            // assignable by users with GRANT rights on the tool
                            $qb->expr()->exists($subQb),

                            // assignable by owners of the badge if enabled
                            $qb->expr()->andX(
                                $qb->expr()->eq('recipient.id', $user->getId()),
                                $qb->expr()->eq('obj.issuingPeer', true)
                            )
                        ))
                            ->setParameter(':grantMask', $grantDecoder->getValue())
                            ->setParameter(':userRoles', $this->tokenStorage->getToken()->getRoleNames())
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
