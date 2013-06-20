<?php

namespace Claroline\CoreBundle\Library\WorkspaceTag;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Repository\WorkspaceTagRepository;
use Claroline\CoreBundle\Repository\RelWorkspaceTagRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workspace_tag.manager")
 */
class Manager
{
    private $tagRepo;
    private $relTagRepo;
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "tagRepo" = @DI\Inject("workspace_tag_repository"),
     *     "relTagRepo" = @DI\Inject("rel_workspace_tag_repository"),
     *     "writer" = @DI\Inject("claroline.workspace_tag.writer")
     * })
     */
    public function __contruct(
        WorkspaceTagRepository $tagRepo,
        RelWorkspaceTagRepository $relTagRepo,
        Writer $writer
    )
    {
        $this->tagRepo = $tagRepo;
        $this->relTagRepo = $relTagRepo;
        $this->writer = $writer;
    }

    public function persist(WorkspaceTag $tag) {
        $this->writer->persist($tag);
    }

    public function createTag($name, User $user = null)
    {
        return $this->writer->createTag($name, $user);
    }

    public function deleteTag(WorkspaceTag $tag)
    {
        $this->writer->deleteTag($tag);
    }

    public function deleteAllTags(User $user = null)
    {
        $tags = $this->tagRepo->findByUser($user);

        foreach ($tags as $tag) {
            $this->writer->deleteTag($tag);
        }
    }

    public function createTagRelation(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        return $this->writer->createTagRelation($tag, $workspace);
    }

    public function deleteTagRelation(RelWorkspaceTag $relWorkspaceTag)
    {
        $this->writer->deleteTagRelation($relWorkspaceTag);
    }

    public function deleteRelWorkspaceTag(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $relWorkspaceTag = $this->relTagRepo->findBy(array('tag' => $tag, 'workspace' => $workspace));

        $this->writer->deleTagRelation($relWorkspaceTag);
    }

    public function createTagHierarchy(WorkspaceTag $tag, WorkspaceTag $parent, $level)
    {
        return $this->writer->createTagHierarchy($tag, $parent, $level);
    }

    public function deleteTagHierarchy(WorkspaceTagHierarchy $tagHierarchy)
    {
        $this->writer->deleteTagHierarchy($tagHierarchy);
    }
}