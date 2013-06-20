<?php

namespace Claroline\CoreBundle\Library\WorkspaceTag;

use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workspace_tag.writer")
 */
class Writer
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createTag($name, User $user = null)
    {
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setName($name);
        $workspaceTag->setUser($user);
        $this->em->persist($workspaceTag);

        return $workspaceTag;
    }

    public function deleteTag(WorkspaceTag $tag)
    {
        $this->em->remove($tag);
    }

    public function createTagRelation(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $relWorkspaceTag = new RelWorkspaceTag();
        $relWorkspaceTag->setTag($tag);
        $relWorkspaceTag->setWorkspace($workspace);
        $this->em->persist($relWorkspaceTag);

        return $relWorkspaceTag;
    }

    public function deleteTagRelation(RelWorkspaceTag $relWorkspaceTag)
    {
        $this->em->remove($relWorkspaceTag);
    }

    public function createTagHierarchy(WorkspaceTag $tag, WorkspaceTag $parent, $level)
    {
        $tagHierarchy = new WorkspaceTagHierarchy();
        $tagHierarchy->setTag($tag);
        $tagHierarchy->setParent($parent);
        $tagHierarchy->setLevel($level);
        $tagHierarchy->setUser($tag->getUser());
        $this->em->persist($tagHierarchy);

        return $tagHierarchy;
    }

    public function deleteTagHierarchy(WorkspaceTagHierarchy $tagHierarchy)
    {
        $this->em->remove($tagHierarchy);
    }
}