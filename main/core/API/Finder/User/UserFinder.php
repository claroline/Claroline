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

use Claroline\AppBundle\API\FinderInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
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

    /** @var ObjectManager */
    private $om;

    /**
     * UserFinder constructor.
     *
     * @DI\InjectParams({
     *     "authChecker"      = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface         $tokenStorage
     * @param WorkspaceManager              $workspaceManager
     * @param ObjectManager                 $om
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager,
        ObjectManager $om
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        if (isset($searches['contactable'])) {
            $qb = $this->getContactableUsers($qb);
            unset($searches['contactable']);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'name':
                    $qb->andWhere('UPPER(obj.username) LIKE :name OR UPPER(CONCAT(obj.firstName, \' \', obj.lastName)) LIKE :name');
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;
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
                  //non recursive search here
                  case 'organization':
                   $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                   $qb->leftJoin('oref.organization', 'o');
                   $qb->andWhere('o.uuid IN (:organizationIds)');
                   $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                   break;

                case 'recursiveOrXOrganization':
                    $value = is_array($filterValue) ? $filterValue : [$filterValue];
                    $roots = $this->om->findList('Claroline\CoreBundle\Entity\Organization\Organization', 'uuid', $value);

                    $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                    $qb->leftJoin('oref.organization', 'oparent');
                    $qb->leftJoin('oref.organization', 'organization');

                    foreach ($roots as $root) {
                        $expr[] = $qb->expr()->andX(
                          $qb->expr()->gte('organization.lft', $root->getLeft()),
                          $qb->expr()->lte('organization.rgt', $root->getRight()),
                          $qb->expr()->eq('oparent.root', $root->getRoot())
                        );
                    }

                    $orX = $qb->expr()->orX(...$expr);
                    $qb->andWhere($orX);
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
                //case 'contactable':
                case 'workspace':
                    $qb->leftJoin('obj.roles', 'wsuroles');
                    $qb->leftJoin('wsuroles.workspace', 'rws');
                    $qb->leftJoin('obj.groups', 'wsugrps');
                    $qb->leftJoin('wsugrps.roles', 'guroles');
                    $qb->leftJoin('guroles.workspace', 'grws');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('rws.uuid', ':workspaceId'),
                        $qb->expr()->eq('grws.uuid', ':workspaceId')
                    ));
                    $qb->setParameter('workspaceId', $filterValue);
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

        // manages custom sort properties
        if (!empty($sortBy) && 0 !== $sortBy['direction']) {
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

    private function getContactableUsers(QueryBuilder $qb)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $organizationsIds = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $currentUser->getOrganizations());
        $workspacesIds = array_map(function (Workspace $workspace) {
            return $workspace->getUuid();
        }, $this->workspaceManager->getWorkspacesByUser($currentUser));

        // same organizations
        $qb->leftJoin('obj.userOrganizationReferences', 'oref');
        $qb->leftJoin('oref.organization', 'o');
        $qb->orWhere('o.uuid IN (:organizationIds)');
        $qb->setParameter('organizationIds', $organizationsIds);

        // same workspaces
        $qb->leftJoin('obj.roles', 'ur');
        $qb->leftJoin('obj.groups', 'ug');
        $qb->leftJoin('ug.roles', 'ugr');
        $qb->leftJoin('ur.workspace', 'urw');
        $qb->leftJoin('ugr.workspace', 'ugrw');
        $qb->orWhere($qb->expr()->orX(
            $qb->expr()->in('urw.uuid', ':workspacesIds'),
            $qb->expr()->in('ugrw.uuid', ':workspacesIds')
        ));
        $qb->setParameter('workspacesIds', $workspacesIds);

        return $qb;
    }
}
