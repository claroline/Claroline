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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class UserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return User::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $roleJoin = false;
        $groupJoin = false;
        $groupRoleJoin = false;

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

                case 'emails':
                    $qb->orWhere($qb->expr()->orX(
                        $qb->expr()->in('obj.email', ':globalSearch'),
                        $qb->expr()->in('obj.username', ':globalSearch')
                    ));

                    $data = array_map(function ($data) {
                        //trim and do other stuff here
                        return $data;
                    }, str_getcsv($filterValue));

                    $qb->setParameter('emails', $data);
                    break;

                case 'isDisabled':
                    $qb->andWhere('obj.isEnabled = :enabled');
                    $qb->setParameter('enabled', !$filterValue);
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

                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
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
                    $qb->setParameters([
                        'roleIds' => is_array($filterValue) ? $filterValue : [$filterValue],
                        'groupRoleIds' => is_array($filterValue) ? $filterValue : [$filterValue],
                    ]);
                    break;

                case 'roleTranslation':
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }

                    $qb->andWhere('UPPER(r.translationKey) LIKE :roleTranslation');
                    $qb->setParameter('roleTranslation', '%'.strtoupper($filterValue).'%');
                    break;

                case 'organization':
                case 'organizations':
                   $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                   $qb->leftJoin('oref.organization', 'o');
                   $qb->andWhere('o.uuid IN (:organizationIds)');
                   $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                   break;

                case 'recursiveOrXOrganization':
                    $value = is_array($filterValue) ? $filterValue : [$filterValue];
                    $roots = $this->om->findList(Organization::class, 'uuid', $value);

                    if (count($roots) > 0) {
                        $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                        $qb->leftJoin('oref.organization', 'oparent');
                        $qb->leftJoin('oref.organization', 'organization');

                        $expr = [];
                        foreach ($roots as $root) {
                            $expr[] = $qb->expr()->andX(
                                $qb->expr()->gte('organization.lft', $root->getLeft()),
                                $qb->expr()->lte('organization.rgt', $root->getRight()),
                                $qb->expr()->eq('oparent.root', $root->getRoot())
                            );
                        }

                        $qb->andWhere($qb->expr()->orX(...$expr));
                    } else {
                        //no roots mean no user so we stop it here and make a crazy search
                        $qb->andWhere('obj.id = -1');

                        return $qb;
                    }
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

                case 'workspace':
                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    // check if user a WS role
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
                    $qb->setParameters([
                        'userWorkspaceId' => $filterValue,
                        'groupWorkspaceId' => $filterValue,
                    ]);
                    break;

                // get users which are manager of at least one workspace (not their personal ws)
                // used by Workspace\ListManagersExporter
                case 'workspaceManager':
                    if (!$roleJoin) {
                        $qb->leftJoin('obj.roles', 'r');
                        $roleJoin = true;
                    }
                    $qb->leftJoin('r.workspace', 'rw');

                    $qb->andWhere('UPPER(rn.name) LIKE :managerRoleName');
                    $qb->setParameter('managerRoleName', 'ROLE_WS_MANAGER_%');
                    $qb->andWhere('rn.type = 2');

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

        // if we don't explicitly request for it, we will not return disabled or removed users
        if (!in_array('isDisabled', array_keys($searches)) && !in_array('isEnabled', array_keys($searches))) {
            $qb->andWhere('obj.isEnabled = TRUE');
        }

        if (!in_array('isRemoved', array_keys($searches))) {
            $qb->andWhere('obj.isRemoved = FALSE');
        }

        $this->sortBy($qb, $sortBy);

        return $qb;
    }

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

    public function getExtraFieldMapping()
    {
        return [
            'name' => 'last_name',
            'isDisabled' => 'is_enabled',
        ];
    }
}
