<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Repository\WorkspaceTagRepository;
use Claroline\CoreBundle\Repository\RelWorkspaceTagRepository;
use Claroline\CoreBundle\Writer\WorkspaceTagWriter;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_tag_manager")
 */
class WorkspaceTagManager
{
    private $tagRepo;
    private $relTagRepo;
    private $workspaceTagWriter;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "tagRepo" = @DI\Inject("workspace_tag_repository"),
     *     "relTagRepo" = @DI\Inject("rel_workspace_tag_repository"),
     *     "workspaceTagWriter" = @DI\Inject("claroline.writer.workspace_tag_writer")
     * })
     */
    public function __contruct(
        WorkspaceTagRepository $tagRepo,
        RelWorkspaceTagRepository $relTagRepo,
        WorkspaceTagWriter $workspaceTagWriter
    )
    {
        $this->tagRepo = $tagRepo;
        $this->relTagRepo = $relTagRepo;
        $this->workspaceTagWriter = $workspaceTagWriter;
    }

    public function insert(WorkspaceTag $tag)
    {
        $this->workspaceTagWriter->insert($tag);
    }

    public function createTag($name, User $user = null)
    {
        return $this->workspaceTagWriter->createTag($name, $user);
    }

    public function deleteTag(WorkspaceTag $tag)
    {
        $this->workspaceTagWriter->deleteTag($tag);
    }

    public function deleteAllTags(User $user = null)
    {
        $tags = $this->tagRepo->findByUser($user);

        foreach ($tags as $tag) {
            $this->workspaceTagWriter->deleteTag($tag);
        }
    }

    public function createTagRelation(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        return $this->workspaceTagWriter->createTagRelation($tag, $workspace);
    }

    public function deleteTagRelation(RelWorkspaceTag $relWorkspaceTag)
    {
        $this->workspaceTagWriter->deleteTagRelation($relWorkspaceTag);
    }

    public function deleteRelWorkspaceTag(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $relWorkspaceTag = $this->relTagRepo->findBy(array('tag' => $tag, 'workspace' => $workspace));

        $this->workspaceTagWriter->deleTagRelation($relWorkspaceTag);
    }

    public function createTagHierarchy(WorkspaceTag $tag, WorkspaceTag $parent, $level)
    {
        return $this->workspaceTagWriter->createTagHierarchy($tag, $parent, $level);
    }

    public function deleteTagHierarchy(WorkspaceTagHierarchy $tagHierarchy)
    {
        $this->workspaceTagWriter->deleteTagHierarchy($tagHierarchy);
    }
}