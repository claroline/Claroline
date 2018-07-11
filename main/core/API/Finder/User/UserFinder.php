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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.user")
 * @DI\Tag("claroline.finder")
 */
class UserFinder extends AbstractFinder
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
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "em"               = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface         $tokenStorage
     * @param WorkspaceManager              $workspaceManager
     * @param EntityManager                 $em
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager,
        EntityManager $em
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
        $this->_em = $em;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
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
                case 'id':
                    $qb->andWhere('obj.uuid IN (:userUuids)');
                    $qb->setParameter('userUuids', is_array($filterValue) ? $filterValue : [$filterValue]);
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

                    if (count($roots) > 0) {
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
                //case 'contactable':
                case 'workspace':
                    //this one is REALLY tricky for performance reasons.
                    //we need to make a union but it's not supported by the querybuilder.
                    //It's not supported at all by doctrine actually.
                    //Let's return a query object with the correct sql.
                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['_workspace_user'] = $filterValue;
                    $byGroupSearch['_workspace_group'] = $filterValue;
                    unset($byUserSearch['workspace']);
                    unset($byGroupSearch['workspace']);
                    $qbUser = $this->om->createQueryBuilder();
                    $qbUser->select('DISTINCT obj')->from($this->getClass(), 'obj');
                    $this->configureQueryBuilder($qbUser, $byUserSearch, $sortBy);
                    //this is our first part of the union
                    $sqlUser = $qbUser->getQuery()->getSql();
                    $sqlUser = $this->removeAlias($sqlUser);
                    $qbGroup = $this->om->createQueryBuilder();
                    $qbGroup->select('DISTINCT obj')->from($this->getClass(), 'obj');
                    $this->configureQueryBuilder($qbGroup, $byGroupSearch, $sortBy);
                    //this is the second part of the union
                    $sqlGroup = $qbGroup->getQuery()->getSql();
                    $sqlGroup = $this->removeAlias($sqlGroup);
                    $together = $sqlUser.' UNION '.$sqlGroup;
                    //we might want to add a count somehere here
                    //add limit & offset too

                    if ($options['count']) {
                        $together = "SELECT COUNT(*) as count FROM ($together) AS wathever";
                        $rsm = new ResultSetMapping();
                        $rsm->addScalarResult('count', 'count', 'integer');
                        $query = $this->_em->createNativeQuery($together, $rsm);
                    } else {
                        //add page & limit
                        if ($options['limit'] > -1) {
                            $together .= ' LIMIT '.$options['limit'];
                        }

                        if ($options['limit'] > 0) {
                            $offset = $options['limit'] * $options['page'];
                            $together .= ' OFFSET  '.$offset;
                        }

                        $rsm = new ResultSetMappingBuilder($this->_em);
                        $rsm->addRootEntityFromClassMetadata($this->getClass(), 'c0_');
                        $query = $this->_em->createNativeQuery($together, $rsm);
                    }

                    return $query;
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
                case 'blacklist':
                    $qb->andWhere("obj.uuid NOT IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'groupName':
                    $qb->join('obj.groups', 'gn');
                    $qb->andWhere("UPPER(gn.name) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
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

    //should be cleaner
    private function removeAlias($sql)
    {
        $aliases = [
          'AS id_0',
          'AS first_name_1',
          'AS last_name_2',
          'AS username_3',
          'AS password_4',
          'AS locale_5',
          'AS salt_6',
          'AS phone_7',
          'AS mail_8',
          'AS administrative_code_9',
          'AS creation_date_10',
          'AS last_login_11',
          'AS initialization_date_12',
          'AS reset_password_13',
          'AS hash_time_14',
          'AS picture_15',
          'AS description_16',
          'AS hasAcceptedTerms_17',
          'AS is_enabled_18',
          'AS is_removed_19',
          'AS is_mail_notified_20',
          'AS is_mail_validated_21',
          'AS hide_mail_warning_22',
          'AS last_uri_23',
          'AS public_url_24',
          'AS has_tuned_public_url_25',
          'AS expiration_date_26',
          'AS authentication_27',
          'AS email_validation_hash_28',
          'AS uuid_29',
          'AS workspace_id_30',
          'AS options_id_31',
        ];

        foreach ($aliases as $alias) {
            $sql = str_replace($alias, '', $sql);
        }

        return $sql;
    }
}
