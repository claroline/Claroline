<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.workspace_writer")
 */
class WorkspaceWriter
{
    /** @var EntityManager */
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

    public function create($name, $code, $isPublic)
    {
        $workspace = new SimpleWorkspace();
        $workspace->setName($name);
        $workspace->setPublic($isPublic);
        $workspace->setCode($code);
        $this->em->persist($workspace);
        $this->em->flush();

        return $workspace;
    }
}