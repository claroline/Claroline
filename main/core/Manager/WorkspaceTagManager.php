<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\RelWorkspaceTagRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\WorkspaceTagHierarchyRepository;
use Claroline\CoreBundle\Repository\WorkspaceTagRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_tag_manager")
 */
class WorkspaceTagManager
{
    /** @var WorkspaceTagRepository */
    private $tagRepo;
    /** @var RelWorkspaceTagRepository */
    private $relTagRepo;
    /** @var WorkspaceTagHierarchyRepository */
    private $tagHierarchyRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    private $roleManager;
    private $workspaceManager;
    private $om;
    private $pagerFactory;
    private $workspaceQueueRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"  = @DI\Inject("claroline.manager.workspace_manager"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"      = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ObjectManager $om,
        PagerFactory $pagerFactory
    ) {
        $this->tagRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag');
        $this->relTagRepo = $om->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag');
        $this->tagHierarchyRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->workspaceQueueRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceRegistrationQueue');
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
    }

    /**
     * Persists and flush a tag.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\WorkspaceTag $tag
     */
    public function insert(WorkspaceTag $tag)
    {
        $this->om->persist($tag);
        $this->om->flush();
    }

    public function createTag($name, User $user = null)
    {
        $tag = new WorkspaceTag();
        $tag->setName($name);
        $tag->setUser($user);
        $this->om->persist($tag);
        $this->om->flush();

        return $tag;
    }

    public function deleteTag(WorkspaceTag $workspaceTag)
    {
        $this->om->remove($workspaceTag);
        $this->om->flush();
    }

    public function linkWorkspace(
        WorkspaceTag $tag,
        Workspace $workspace = null
    ) {
        $tag->setWorkspace($workspace);
        $this->om->persist($tag);
        $this->om->flush();
    }

    public function createTagRelation(WorkspaceTag $tag, Workspace $workspace)
    {
        $relWorkspaceTag = new RelWorkspaceTag();
        $relWorkspaceTag->setTag($tag);
        $relWorkspaceTag->setWorkspace($workspace);
        $this->om->persist($relWorkspaceTag);
        $this->om->flush();

        return $relWorkspaceTag;
    }

    public function deleteTagRelation(RelWorkspaceTag $relWorkspaceTag)
    {
        $this->om->remove($relWorkspaceTag);
        $this->om->flush();
    }

    public function deleteRelWorkspaceTag(WorkspaceTag $tag, Workspace $workspace)
    {
        $relWorkspaceTag = $this->relTagRepo->findOneBy(['tag' => $tag, 'workspace' => $workspace]);

        $this->om->remove($relWorkspaceTag);
        $this->om->flush();
    }

    public function deleteAllRelationsFromWorkspaceAndUser(Workspace $workspace, User $user)
    {
        $relations = $this->relTagRepo->findByWorkspaceAndUser($workspace, $user);

        foreach ($relations as $relation) {
            $this->om->remove($relation);
        }
        $this->om->flush();
    }

    public function deleteAllAdminRelationsFromWorkspace(Workspace $workspace)
    {
        $relations = $this->relTagRepo->findAdminByWorkspace($workspace);

        foreach ($relations as $relation) {
            $this->om->remove($relation);
        }
        $this->om->flush();
    }

    public function createTagHierarchy(WorkspaceTag $tag, WorkspaceTag $parent, $level)
    {
        $tagHierarchy = new WorkspaceTagHierarchy();
        $tagHierarchy->setTag($tag);
        $tagHierarchy->setParent($parent);
        $tagHierarchy->setLevel($level);
        $tagHierarchy->setUser($tag->getUser());
        $this->om->persist($tagHierarchy);
        $this->om->flush();

        return $tagHierarchy;
    }

    public function deleteTagHierarchy(WorkspaceTagHierarchy $tagHierarchy)
    {
        $this->om->remove($tagHierarchy);
        $this->om->flush();
    }

    public function getNonEmptyTagsByUser(User $user)
    {
        return $this->tagRepo->findNonEmptyTagsByUser($user);
    }

    public function getNonEmptyAdminTags()
    {
        return $this->tagRepo->findNonEmptyAdminTags();
    }

    public function getNonEmptyAdminTagsByWorspaces(array $workspaces)
    {
        return $this->tagRepo->findNonEmptyAdminTagsByWorspaces($workspaces);
    }

    public function getPossibleAdminChildren(WorkspaceTag $tag)
    {
        return $this->tagRepo->findPossibleAdminChildren($tag);
    }

    public function getPossibleAdminChildrenPager(WorkspaceTag $tag, $page)
    {
        $datas = $this->tagRepo->findPossibleAdminChildren($tag);

        return $this->pagerFactory->createPagerFromArray($datas, $page);
    }

    public function getPossibleAdminChildrenPagerBySearch(
        WorkspaceTag $tag,
        $page,
        $search
    ) {
        $datas = $this->tagRepo->findPossibleAdminChildrenByName($tag, $search);

        return $this->pagerFactory->createPagerFromArray($datas, $page);
    }

    public function getPossibleChildren(User $user, WorkspaceTag $tag)
    {
        return $this->tagRepo->findPossibleChildren($user, $tag);
    }

    public function getPossibleChildrenPager(User $user, WorkspaceTag $tag, $page)
    {
        $datas = $this->tagRepo->findPossibleChildren($user, $tag);

        return $this->pagerFactory->createPagerFromArray($datas, $page);
    }

    public function getPossibleChildrenPagerBySearch(
        User $user,
        WorkspaceTag $tag,
        $page,
        $search
    ) {
        $datas = $this->tagRepo
            ->findPossibleChildrenByName($user, $tag, $search);

        return $this->pagerFactory->createPagerFromArray($datas, $page);
    }

    public function getAdminChildren(WorkspaceTag $tag)
    {
        return $this->tagRepo->findAdminChildren($tag);
    }

    public function getChildren(User $user, WorkspaceTag $tag)
    {
        return $this->tagRepo->findChildren($user, $tag);
    }

    public function getAdminRootTags()
    {
        return $this->tagRepo->findAdminRootTags();
    }

    public function getRootTags(User $user)
    {
        return $this->tagRepo->findRootTags($user);
    }

    public function getAdminChildrenFromTag(WorkspaceTag $workspaceTag)
    {
        return $this->tagRepo->findAdminChildrenFromTag($workspaceTag);
    }

    public function getAdminChildrenFromTags(array $tags)
    {
        return $this->tagRepo->findAdminChildrenFromTags($tags);
    }

    public function getChildrenFromTag(User $user, WorkspaceTag $workspaceTag)
    {
        return $this->tagRepo->findChildrenFromTag($user, $workspaceTag);
    }

    public function getChildrenFromTags(User $user, array $tags)
    {
        return $this->tagRepo->findChildrenFromTags($user, $tags);
    }

    public function getAdminParentsFromTag(WorkspaceTag $tag)
    {
        return $this->tagRepo->findAdminParentsFromTag($tag);
    }

    public function getParentsFromTag(User $user, WorkspaceTag $tag)
    {
        return $this->tagRepo->findParentsFromTag($user, $tag);
    }

    public function getWorkspaceTagFromIds(array $tagIds)
    {
        return $this->tagRepo->findWorkspaceTagFromIds($tagIds);
    }

    public function getTagsByUser(User $user = null)
    {
        return $this->tagRepo->findBy(['user' => $user], ['name' => 'ASC']);
    }

    public function getTagByNameAndUser($name, User $user = null)
    {
        return $this->tagRepo->findOneBy(
            [
                'name' => $name,
                'user' => $user,
            ]
        );
    }

    public function getAdminTagById($tagId)
    {
        return $this->tagRepo->findOneBy(['id' => $tagId, 'user' => null]);
    }

    public function getTagRelationsByWorkspaceAndUser(Workspace $workspace, User $user)
    {
        return $this->relTagRepo->findByWorkspaceAndUser($workspace, $user);
    }

    public function getAdminTagRelationsByWorkspace(Workspace $workspace)
    {
        return $this->relTagRepo->findAdminByWorkspace($workspace);
    }

    public function getAdminTagRelationsByTag(WorkspaceTag $tag)
    {
        return $this->relTagRepo->findAdminByTag($tag);
    }

    public function getTagRelationByWorkspaceAndTagAndUser(
        Workspace $workspace,
        WorkspaceTag $tag,
        User $user
    ) {
        return $this->relTagRepo->findOneByWorkspaceAndTagAndUser($workspace, $tag, $user);
    }

    public function getAdminTagRelationByWorkspaceAndTag(Workspace $workspace, WorkspaceTag $tag)
    {
        return $this->relTagRepo->findOneAdminByWorkspaceAndTag($workspace, $tag);
    }

    public function getAllTagRelationsByWorkspaceAndUser(Workspace $workspace, User $user)
    {
        return $this->relTagRepo->findAllByWorkspaceAndUser($workspace, $user);
    }

    public function getTagRelationsByUser(User $user)
    {
        return $this->relTagRepo->findByUser($user);
    }

    public function getTagRelationsByAdmin()
    {
        return $this->relTagRepo->findByAdmin();
    }

    public function getTagRelationsByAdminAndWorkspaces(array $workspaces)
    {
        return $this->relTagRepo->findByAdminAndWorkspaces($workspaces);
    }

    public function getAdminHierarchiesByParent(WorkspaceTag $workspaceTag)
    {
        return $this->tagHierarchyRepo->findAdminHierarchiesByParent($workspaceTag);
    }

    public function getAdminHierarchiesByParents(array $parents)
    {
        return $this->tagHierarchyRepo->findAdminHierarchiesByParents($parents);
    }

    public function getHierarchiesByParent(User $user, WorkspaceTag $workspaceTag)
    {
        return $this->tagHierarchyRepo->findHierarchiesByParent($user, $workspaceTag);
    }

    public function getHierarchiesByParents(User $user, array $parents)
    {
        return $this->tagHierarchyRepo->findHierarchiesByParents($user, $parents);
    }

    public function getAdminHierarchiesByParentsAndChildren(array $parents, array $children)
    {
        return $this->tagHierarchyRepo->findAdminHierarchiesByParentsAndChildren($parents, $children);
    }

    public function getHierarchiesByParentsAndChildren(User $user, array $parents, array $children)
    {
        return $this->tagHierarchyRepo->findHierarchiesByParentsAndChildren($user, $parents, $children);
    }

    public function getAllHierarchiesByUser(User $user)
    {
        return $this->tagHierarchyRepo->findAllByUser($user);
    }

    public function getAllAdminHierarchies()
    {
        return $this->tagHierarchyRepo->findAllAdmin();
    }

    public function getHierarchiesByUserAndTag(WorkspaceTag $tag, User $user = null)
    {
        return $this->tagHierarchyRepo->findBy(['user' => $user, 'tag' => $tag]);
    }

    public function getHierarchiesByTag(WorkspaceTag $tag)
    {
        return $this->tagHierarchyRepo->findBy(['tag' => $tag]);
    }

    public function getDatasForWorkspaceList(
        $withRoles = true,
        $search = '',
        $max = 20,
        $wsMax = 10
    ) {
        if (empty($search)) {
            $workspaces = $this->workspaceRepo->findDisplayableWorkspaces();
        } else {
            $workspaces = $this->workspaceRepo
                ->findDisplayableWorkspacesBySearch($search);
        }
        $nonPersonalWs = $this->workspaceManager
            ->getDisplayableNonPersonalWorkspaces(1, $max, $search);
        $personalWs = $this->workspaceManager
            ->getDisplayablePersonalWorkspaces(1, $max, $search);
        $tags = $this->getNonEmptyAdminTags();
        $relTagWorkspace = $this->getTagRelationsByAdmin();
        $tagWorkspaces = [];

        // create an array: tagId => [associated_workspace_relation]
        foreach ($relTagWorkspace as $tagWs) {
            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = [];
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }

        $tagsHierarchy = $this->getAllAdminHierarchies();
        $rootTags = $this->getAdminRootTags();
        $hierarchy = [];

        // create an array : tagId => [direct_children_id]
        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = [];
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        // create an array indicating which tag is displayable
        // a tag is displayable if it or one of his children contains is associated to a workspace
        $displayable = [];
        $allAdminTags = $this->getTagsByUser(null);

        foreach ($allAdminTags as $adminTag) {
            $adminTagId = $adminTag->getId();
            $displayable[$adminTagId] = $this->isTagDisplayable($adminTag, $tagWorkspaces, $hierarchy);
        }

        $workspaceRoles = [];

        if ($withRoles) {
            $roles = $this->roleManager->getAllWhereWorkspaceIsDisplayable();

            foreach ($roles as $role) {
                $wsRole = $role->getWorkspace();

                if (!is_null($wsRole)) {
                    $code = $wsRole->getCode();

                    if (!isset($workspaceRoles[$code])) {
                        $workspaceRoles[$code] = [];
                    }

                    $workspaceRoles[$code][] = $role;
                }
            }
        }

        $tagWorkspacePager = [];

        foreach ($tagWorkspaces as $key => $content) {
            $tagWorkspacePager[$key] = $this->pagerFactory->createPagerFromArray($content, 1);
        }

        return [
            'workspaces' => $this->pagerFactory->createPagerFromArray($workspaces, 1, $wsMax),
            'tags' => $tags,
            'tagWorkspaces' => $tagWorkspacePager,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
            'displayable' => $displayable,
            'workspaceRoles' => $workspaceRoles,
            'search' => $search,
            'nonPersonalWs' => $nonPersonalWs,
            'personalWs' => $personalWs,
            'max' => $max,
            'wsMax' => $wsMax,
        ];
    }

    public function getDatasForWorkspaceListByUser(User $user, array $roles)
    {
        $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);
        $tags = $this->tagRepo->findNonEmptyTagsByUser($user);
        $relTagWorkspace = $this->relTagRepo->findByUser($user);
        $tagWorkspaces = [];

        foreach ($relTagWorkspace as $tagWs) {
            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = [];
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }
        $tagsHierarchy = $this->tagHierarchyRepo->findAllByUser($user);
        $rootTags = $this->tagRepo->findRootTags($user);
        $hierarchy = [];

        // create an array : tagId => [direct_children_id]
        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = [];
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        // create an array indicating which tag is displayable
        // a tag is displayable if it or one of his children contains is associated to a workspace
        $displayable = [];
        $allTags = $this->tagRepo->findBy(['user' => $user], ['name' => 'ASC']);

        foreach ($allTags as $oneTag) {
            $oneTagId = $oneTag->getId();
            $displayable[$oneTagId] = $this->isTagDisplayable($oneTag, $tagWorkspaces, $hierarchy);
        }

        $tagWorkspacePager = [];

        foreach ($tagWorkspaces as $key => $content) {
            $tagWorkspacePager[$key] = $this->pagerFactory->createPagerFromArray($content, 1);
        }

        $datas = [];
        $datas['workspaces'] = $workspaces;
        $datas['tags'] = $tags;
        $datas['tagWorkspaces'] = $tagWorkspaces;
        $datas['hierarchy'] = $hierarchy;
        $datas['rootTags'] = $rootTags;
        $datas['displayable'] = $displayable;

        return $datas;
    }

    /**
     * Returns all datas necessary to display the list of all workspaces visible for all users
     * that are open for self-registration.
     */
    public function getDatasForSelfRegistrationWorkspaceList(User $user, $search = '')
    {
        $workspaceQueue = $this->workspaceQueueRepo->findByUser($user);
        $listworkspacePending = [];

        foreach ($workspaceQueue as $w) {
            $listworkspacePending[$w->getWorkspace()->getId()] = $w->getWorkspace()->getId();
        }

        if (empty($search)) {
            $workspaces = $this->workspaceRepo->findWorkspacesWithSelfRegistration($user);
        } else {
            $workspaces = $this->workspaceRepo
                 ->findWorkspacesWithSelfRegistrationBySearch($user, $search);
        }
        $tags = $this->getNonEmptyAdminTags();

        try {
            $relTagWorkspace = $this->getTagRelationsByAdminAndWorkspaces($workspaces);
        } catch (\InvalidArgumentException $e) {
            $relTagWorkspace = [];
        }

        $tagWorkspaces = [];

        // create an array: tagId => [associated_workspace_relation]
        foreach ($relTagWorkspace as $tagWs) {
            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = [];
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }

        $tagsHierarchy = $this->getAllAdminHierarchies();
        $rootTags = $this->getAdminRootTags();
        $hierarchy = [];

        // create an array : tagId => [direct_children_id]
        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = [];
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        // create an array indicating which tag is displayable
        // a tag is displayable if it or one of his children contains is associated to a workspace
        $displayable = [];
        $allAdminTags = $this->getTagsByUser(null);

        foreach ($allAdminTags as $adminTag) {
            $adminTagId = $adminTag->getId();
            $displayable[$adminTagId] = $this->isTagDisplayable($adminTag, $tagWorkspaces, $hierarchy);
        }

        $tagWorkspacePager = [];

        foreach ($tagWorkspaces as $key => $content) {
            $tagWorkspacePager[$key] = $this->pagerFactory->createPagerFromArray($content, 1);
        }

        return [
            'user' => $user,
            'workspaces' => $this->pagerFactory->createPagerFromArray($workspaces, 1),
            'tags' => $tags,
            'tagWorkspaces' => $tagWorkspacePager,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
            'displayable' => $displayable,
            'listworkspacePending' => $listworkspacePending,
            'search' => $search,
        ];
    }

    /**
     * Checks if given tag or at least one of its children is associated to a workspace.
     *
     * @param int   $tagId
     * @param array $tagWorkspaces
     * @param array $hierarchy
     *
     * @return bool
     */
    private function isTagDisplayable(WorkspaceTag $tag, array $tagWorkspaces, array $hierarchy)
    {
        $displayable = false;
        $tagId = $tag->getId();

        if ((isset($tagWorkspaces[$tagId]) && count($tagWorkspaces[$tagId]) > 0)
            || !is_null($tag->getWorkspace())) {
            $displayable = true;
        } else {
            if (isset($hierarchy[$tagId]) && count($hierarchy[$tagId]) > 0) {
                $children = $hierarchy[$tagId];

                foreach ($children as $child) {
                    $displayable = $this->isTagDisplayable($child, $tagWorkspaces, $hierarchy);

                    if ($displayable) {
                        break;
                    }
                }
            }
        }

        return $displayable;
    }

    public function getPagerRelationByTag(WorkspaceTag $workspaceTag, $page = 1)
    {
        $relations = $this->relTagRepo->findAdminRelationsByTag($workspaceTag);

        return $this->pagerFactory->createPagerFromArray($relations, $page);
    }

    public function getPagerRelationByTagForSelfReg(WorkspaceTag $workspaceTag, $page = 1)
    {
        $relations = $this->relTagRepo->findAdminRelationsByTagForSelfReg($workspaceTag);

        return $this->pagerFactory->createPagerFromArray($relations, $page);
    }

    public function getPagerAllWorkspaces($page = 1)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspaces();

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }

    public function getPagerAllWorkspacesWithSelfReg(User $user, $page = 1)
    {
        $workspaces = $this->workspaceRepo->findWorkspacesWithSelfRegistration($user);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }
}
