<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\User;

use Claroline\CoreBundle\API\FinderInterface;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.user")
 * @DI\Tag("claroline.finder")
 */
class UserFinder implements FinderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * UserFinder constructor.
     *
     * @DI\InjectParams({
     *     "authChecker"      = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface         $tokenStorage
     * @param WorkspaceManager              $workspaceManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        if (isset($searches['contactable'])) {
            $qb = $this->getContactableUsers($qb);
        } elseif (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->leftJoin('obj.userOrganizationReferences', 'oaref');
            $qb->leftJoin('oaref.organization', 'uo');
            $qb->leftJoin('uo.administrators', 'ua');
            $qb->andWhere('ua.id = :userId');
            $qb->setParameter('userId', $currentUser->getId());
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'isDisabled':
                    $qb->andWhere('obj.isEnabled = :isEnabled');
                    $qb->setParameter('isEnabled', !$filterValue);
                    break;
                case 'hasPersonalWorkspace':
                    $qb->andWhere('obj.personalWorkspace IS NOT NULL');
                    break;
                case 'group':
                    $qb->leftJoin('obj.groups', 'g');
                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'scheduledtask':
                    $qb->leftJoin('obj.scheduledTasks', 'st');
                    $qb->andWhere('st.id IN (:scheduledTasks)');
                    $qb->setParameter('scheduledTasks', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'role':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere('r.uuid IN (:roleIds)');
                    $qb->setParameter('roleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'organization':
                    $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                    $qb->leftJoin('oref.organization', 'o');
                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'location':
                    $qb->leftJoin('obj.locations', 'l');
                    $qb->andWhere('l.uuid IN (:locationIds)');
                    $qb->setParameter('locationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'organizationManager':
                    $qb->leftJoin('obj.administratedOrganizations', 'ao');
                    $qb->andWhere('ao.uuid IN (:administratedOrganizations)');
                    $qb->setParameter('administratedOrganizations', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'contactable':
                    break;
                case 'blacklist':
                    $qb->andWhere("obj.uuid NOT IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }

        if (!in_array('isRemoved', array_keys($searches))) {
            $qb->andWhere('obj.isRemoved = FALSE');
        }

        if (!empty($sortBy) && 'isDisabled' === $sortBy['property'] && 0 !== $sortBy['direction']) {
            $qb->orderBy('obj.isEnabled ', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
        }

        return $qb;
    }

    private function getContactableUsers(QueryBuilder $qb)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $organizationsIds = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $currentUser->getOrganizations());
        $workspacesIds = array_map(function (Workspace $workspace) {
            return $workspace->getUuid();
        }, $this->workspaceManager->getWorkspacesByUser($currentUser));

        $qb->leftJoin('obj.organizations', 'uo');
        $qb->orWhere('uo.uuid IN (:orgaIds)');
        $qb->setParameter('orgaIds', $organizationsIds);

        $qb->leftJoin('obj.roles', 'ur');
        $qb->leftJoin('obj.groups', 'ug');
        $qb->leftJoin('ug.roles', 'ugr');
        $qb->leftJoin('ur.workspace', 'urw');
        $qb->leftJoin('ugr.workspace', 'ugrw');
        $qb->orWhere('urw.uuid IN (:workspacesIds)');
        $qb->orWhere('ugrw.uuid IN (:workspacesIds)');
        $qb->setParameter('workspacesIds', $workspacesIds);

        return $qb;
    }
}
