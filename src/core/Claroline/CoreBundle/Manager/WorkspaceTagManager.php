<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Repository\RelWorkspaceTagRepository;
use Claroline\CoreBundle\Database\Writer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_tag_manager")
 */
class WorkspaceTagManager
{
    private $relTagRepo;
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "relTagRepo" = @DI\Inject("rel_workspace_tag_repository"),
     *     "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __contruct(
        RelWorkspaceTagRepository $relTagRepo,
        Writer $writer
    )
    {
        $this->relTagRepo = $relTagRepo;
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
}