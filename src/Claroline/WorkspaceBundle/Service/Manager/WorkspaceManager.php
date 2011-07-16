<?php

namespace Claroline\WorkspaceBundle\Service\Manager;

use Claroline\WorkspaceBundle\Entity\Workspace;
use Doctrine\ORM\EntityManager;

class WorkspaceManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param string $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $em->getClassMetadata($class)->name;

    }

    public function create(Workspace $ws)
    {
        $this->em->persist($ws);
        $this->em->flush();
    }

    public function delete(Workspace $ws)
    {
        $this->em->remove($ws);
        $this->em->flush();
    }
}