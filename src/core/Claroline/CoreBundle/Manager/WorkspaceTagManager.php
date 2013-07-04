<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Repository\WorkspaceTagRepository;
use Claroline\CoreBundle\Repository\RelWorkspaceTagRepository;
use Claroline\CoreBundle\Repository\WorkspaceTagHierarchyRepository;
use Claroline\CoreBundle\Database\Writer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_tag_manager")
 */
class WorkspaceTagManager
{
    private $tagRepo;
    private $relTagRepo;
    private $tagHierarchyRepo;
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "tagRepo" = @DI\Inject("workspace_tag_repository"),
     *     "relTagRepo" = @DI\Inject("rel_workspace_tag_repository"),
     *     "tagHierarchyRepo" = @DI\Inject("workspace_tag_hierarchy_repository"),
     *     "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __contruct(
        WorkspaceTagRepository $tagRepo,
        RelWorkspaceTagRepository $relTagRepo,
        WorkspaceTagHierarchyRepository $tagHierarchyRepo,
        Writer $writer
    )
    {
        $this->tagRepo = $tagRepo;
        $this->relTagRepo = $relTagRepo;
        $this->tagHierarchyRepo = $tagHierarchyRepo;
        $this->writer = $writer;
    }

    public function insert(WorkspaceTag $tag)
    {
        $this->writer->create($tag);
    }

    public function createTag($name, User $user = null)
    {
        $tag = new WorkspaceTag();
        $tag->setName($name);
        $tag->setUser($user);
        $this->writer->create($tag);

        return $tag;
    }

    public function createTagRelation(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $relWorkspaceTag = new RelWorkspaceTag();
        $relWorkspaceTag->setTag($tag);
        $relWorkspaceTag->setWorkspace($workspace);
        $this->writer->create($relWorkspaceTag);

        return $relWorkspaceTag;
    }

    public function deleteTagRelation(RelWorkspaceTag $relWorkspaceTag)
    {
        $this->writer->delete($relWorkspaceTag);
    }

    public function deleteRelWorkspaceTag(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $relWorkspaceTag = $this->relTagRepo->findBy(array('tag' => $tag, 'workspace' => $workspace));

        $this->writer->delete($relWorkspaceTag);
    }

    public function createTagHierarchy(WorkspaceTag $tag, WorkspaceTag $parent, $level)
    {
        $tagHierarchy = new WorkspaceTagHierarchy();
        $tagHierarchy->setTag($tag);
        $tagHierarchy->setParent($parent);
        $tagHierarchy->setLevel($level);
        $tagHierarchy->setUser($tag->getUser());
        $this->writer->create($tagHierarchy);

        return $tagHierarchy;
    }

    public function deleteTagHierarchy(WorkspaceTagHierarchy $tagHierarchy)
    {
        $this->writer->delete($tagHierarchy);
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

    public function getPossibleChildren(User $user, WorkspaceTag $tag)
    {
        return $this->tagRepo->findPossibleChildren($user, $tag);
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

    public function getAdminChildrenFromTags(array $tags)
    {
        return $this->tagRepo->findAdminChildrenFromTags($tags);
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

    public function getTagRelationsByWorkspaceAndUser(AbstractWorkspace $workspace, User $user)
    {
        return $this->relTagRepo->findByWorkspaceAndUser($workspace, $user);
    }

    public function getAdminTagRelationsByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->relTagRepo->findAdminByWorkspace($workspace);
    }

    public function getTagRelationByWorkspaceAndTagAndUser(
        AbstractWorkspace $workspace,
        WorkspaceTag $tag,
        User $user
    )
    {
        return $this->relTagRepo->findOneByWorkspaceAndTagAndUser($workspace,$tag, $user);
    }

    public function getAdminTagRelationByWorkspaceAndTag(AbstractWorkspace $workspace, WorkspaceTag $tag)
    {
        return $this->relTagRepo->findOneAdminByWorkspaceAndTag($workspace, $tag);
    }

    public function getAllTagRelationsByWorkspaceAndUser(AbstractWorkspace $workspace, User $user)
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

    public function getAdminHierarchiesByParents(array $parents)
    {
        return $this->tagHierarchyRepo->findAdminHierarchiesByParents($parents);
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
}