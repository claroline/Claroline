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

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WorkspaceManager */
    private $workspaceManager;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
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
                case 'id':
                    $qb->andWhere('obj.uuid IN (:userUuids)');
                    $qb->setParameter('userUuids', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'isDisabled':
                    $qb->andWhere('obj.isEnabled = :enabled');
                    $qb->setParameter('enabled', !$filterValue);
                    break;
                case 'hasPersonalWorkspace':
                    $qb->andWhere('obj.personalWorkspace IS NOT NULL');
                    break;
                case 'group':
                    $qb->leftJoin('obj.groups', 'g');
                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'group_name':
                    $qb->leftJoin('obj.groups', 'g');
                    $qb->andWhere('UPPER(g.name) LIKE :groupName');
                    $qb->setParameter('groupName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'scheduledtask': // TODO : should be removed
                    $qb->leftJoin('obj.scheduledTasks', 'st');
                    $qb->andWhere('st.id IN (:scheduledTasks)');
                    $qb->setParameter('scheduledTasks', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'role':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere('r.uuid IN (:roleIds)');
                    $qb->setParameter('roleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'unionRole':
                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['role'] = $filterValue;
                    $byGroupSearch['_roleGroup'] = $filterValue;
                    unset($byUserSearch['unionRole']);
                    unset($byGroupSearch['unionRole']);

                    return $this->union($byUserSearch, $byGroupSearch, $options, $sortBy);

                    break;
                case '_roleGroup':
                    $qb->leftJoin('obj.groups', 'ggr');
                    $qb->leftJoin('ggr.roles', 'groupRole');
                    $qb->andWhere('groupRole.uuid IN (:groleIds)');
                    $qb->setParameter('groleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'roleTranslation':
                    $qb->leftJoin('obj.roles', 'rn');
                    $qb->andWhere('UPPER(rn.translationKey) LIKE :roleTranslation');
                    $qb->setParameter('roleTranslation', '%'.strtoupper($filterValue).'%');
                    break;
                  //non recursive search here
                case 'organization':
                   $qb->leftJoin('obj.userOrganizationReferences', 'oref');
                   $qb->leftJoin('oref.organization', 'o');
                   $qb->andWhere('o.uuid IN (:organizationIds)');
                   $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                   break;

                case 'organizationNameUser':
                    $qb->leftJoin('obj.userOrganizationReferences', 'orefu');
                    $qb->leftJoin('orefu.organization', 'ou');
                    $qb->andWhere('UPPER(ou.name) LIKE :organizationName');
                    $qb->setParameter('organizationName', '%'.strtoupper($filterValue).'%');
                    break;
                case '_organizationNameGroup':
                    $qb->leftJoin('obj.groups', 'ugroup');
                    $qb->leftJoin('ugroup.organizations', 'ogroup');
                    $qb->andWhere('UPPER(ogroup.name) LIKE :organizationNameGroup');
                    $qb->setParameter('organizationNameGroup', '%'.strtoupper($filterValue).'%');
                    break;
                case 'unionOrganizationName':
                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['organizationNameUser'] = $filterValue;
                    $byGroupSearch['_organizationNameGroup'] = $filterValue;
                    unset($byUserSearch['unionOrganizationName']);
                    unset($byGroupSearch['unionOrganizationName']);

                    return $this->union($byUserSearch, $byGroupSearch, $options, $sortBy);
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

                        $orX = $qb->expr()->orX(...$expr);
                        $qb->andWhere($orX);
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

                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['_workspace_user'] = $filterValue;
                    $byGroupSearch['_workspace_group'] = $filterValue;
                    unset($byUserSearch['workspace']);
                    unset($byGroupSearch['workspace']);

                    return $this->union($byUserSearch, $byGroupSearch, $options, $sortBy);

                    break;
                case '_workspace_user':
                    $filterValue = array_map(function ($value) {
                        return "'$value'";
                    }, $filterValue);
                    $string = join($filterValue, ',');
                    $qb->leftJoin('obj.roles', 'wsuroles');
                    $qb->leftJoin('wsuroles.workspace', 'rws');
                    $qb->andWhere('rws.uuid IN ('.$string.')');
                    break;
                case '_workspace_group':
                    $filterValue = array_map(function ($value) {
                        return "'$value'";
                    }, $filterValue);
                    $string = join($filterValue, ',');
                    $qb->leftJoin('obj.groups', 'grps');
                    $qb->leftJoin('grps.roles', 'grpRole');
                    $qb->leftJoin('grpRole.workspace', 'ws');
                    $qb->andWhere('ws.uuid IN ('.$string.')');
                    break;
                case 'groupName':
                    $qb->join('obj.groups', 'gn');
                    $qb->andWhere("UPPER(gn.name) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
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

    //required for the unions
    public function getExtraFieldMapping()
    {
        return [
            'name' => 'last_name',
            'isDisabled' => 'is_enabled',
        ];
    }
}
