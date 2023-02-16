<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class UserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return User::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $roleJoin = false;
        $groupJoin = false;
        $groupRoleJoin = false;
        $organizationJoin = false;

        $this->addFilter(UserFilter::class, $qb, 'obj', [
            'disabled' => in_array('isDisabled', array_keys($searches)) && $searches['isDisabled'],
        ]);

        if (in_array('isDisabled', array_keys($searches))) {
            unset($searches['isDisabled']);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'username':
                    // because some users use numeric username
                    // if we let the default, the finder will add a strict check instead of a LIKE
                    $qb->andWhere('UPPER(obj.username) LIKE :username');
                    $qb->setParameter('username', '%'.strtoupper($filterValue).'%');
                    break;

                case 'name':
                    $qb->andWhere('UPPER(obj.username) LIKE :name OR UPPER(CONCAT(obj.firstName, \' \', obj.lastName)) LIKE :name');
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;

                case 'id': // should not exist
                    $qb->andWhere('obj.uuid IN (:userUuids)');
                    $qb->setParameter('userUuids', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                case 'hasPersonalWorkspace':
                    $qb->andWhere('obj.personalWorkspace IS NOT NULL');
                    break;

                case 'group':
                case 'groups':
                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }

                    $groups = is_array($filterValue) ? $filterValue : [$filterValue];
                    $groups = array_map(function ($group) {
                        return $group instanceof Group ? $group->getUuid() : $group;
                    }, $groups);

                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', $groups);
                    break;

                case 'role':
                case 'roles':
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }

                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }

                    if (!$groupRoleJoin) {
                        $qb->leftJoin('g.roles', 'gr');
                        $groupRoleJoin = true;
                    }

                    $qb->andWhere('(r.uuid IN (:roleIds) OR gr.uuid IN (:groupRoleIds))');
                    $qb->setParameter('roleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    $qb->setParameter('groupRoleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                // should not exist : used by the users DataSource
                case 'roleTranslation':
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }

                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }

                    if (!$groupRoleJoin) {
                        $qb->leftJoin('g.roles', 'gr');
                        $groupRoleJoin = true;
                    }

                    $qb->andWhere('(UPPER(r.translationKey) LIKE :roleTranslation OR UPPER(gr.translationKey) LIKE :roleTranslation)');
                    $qb->setParameter('roleTranslation', '%'.strtoupper($filterValue).'%');
                    break;

                case 'organization':
                case 'organizations':
                    if (!$organizationJoin) {
                        $qb->leftJoin('obj.userOrganizationReferences', 'ref');
                        $qb->leftJoin('ref.organization', 'o');

                        $organizationJoin = true;
                    }

                    // get organizations from the group
                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }
                    $qb->leftJoin('g.organizations', 'go');

                    $qb->andWhere('(o.uuid IN (:organizations) OR go.uuid IN (:organizations))');
                    $qb->setParameter('organizations', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                case 'location':
                    $qb->leftJoin('obj.locations', 'l');
                    $qb->andWhere('l.uuid IN (:locationIds)');
                    $qb->setParameter('locationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                case 'organizationManager':
                    if (!$organizationJoin) {
                        $qb->leftJoin('obj.userOrganizationReferences', 'ref');
                        $qb->leftJoin('ref.organization', 'o');

                        $organizationJoin = true;
                    }

                    $qb->andWhere('ref.manager = 1');
                    $qb->andWhere('o.uuid IN (:administratedOrganizations)');

                    $qb->setParameter('administratedOrganizations', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;

                case 'workspace': // Avoid using it : directly search by passing the workspace roles (more efficient)
                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    // check if user has a WS role
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }

                    $qb->leftJoin('r.workspace', 'rws');

                    // check if user is in a group with a WS role
                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }

                    if (!$groupRoleJoin) {
                        $qb->leftJoin('g.roles', 'gr');
                        $groupRoleJoin = true;
                    }

                    $qb->leftJoin('gr.workspace', 'gws');
                    $qb->andWhere('(rws.uuid IN (:userWorkspaceId) OR gws.uuid IN (:groupWorkspaceId))');
                    $qb->setParameter('userWorkspaceId', $filterValue);
                    $qb->setParameter('groupWorkspaceId', $filterValue);

                    break;

                // get users which are manager of at least one workspace (not their personal ws)
                // used by Workspace\ListManagersExporter
                case 'workspaceManager':
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }
                    $qb->leftJoin('r.workspace', 'rw');

                    $qb->andWhere('UPPER(r.name) LIKE :managerRoleName');
                    $qb->setParameter('managerRoleName', 'ROLE_WS_MANAGER_%');
                    $qb->andWhere('r.type = 2');

                    $qb->andWhere('rw.model = 0');
                    $qb->andWhere('rw.personal = 0');
                    $qb->andWhere('rw.archived = 0');

                    break;

                case 'resetPasswordHash':
                case 'salt':
                case 'password':
                case 'emailValidationHash':
                    // those are security fields, we don't want someone try to retrieve users with this
                    // because it will leak sensible data.
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        $this->sortBy($qb, $sortBy);

        return $qb;
    }

    private function sortBy(QueryBuilder $qb, array $sortBy = null)
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

    protected function getExtraFieldMapping(): array
    {
        return [
            'name' => 'last_name',
            'isDisabled' => 'is_enabled',
        ];
    }
}
