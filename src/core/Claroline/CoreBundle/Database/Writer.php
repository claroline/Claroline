<?php

namespace Claroline\CoreBundle\Database;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.database.writer")
 */
class Writer
{
    private $em;
    private $isFlushSuspended = false;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create($entity)
    {
        $this->em->persist($entity);
        $this->tryFlush();
    }

    public function update($entity)
    {
        $this->em->persist($entity);
        $this->tryFlush();
    }

    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->tryFlush();
    }

    public function suspendFlush()
    {
        $this->isFlushSuspended = true;
    }

    public function forceFlush()
    {
        $this->isFlushSuspended = false;
        $this->em->flush();
    }

    private function tryFlush()
    {
        if (!$this->isFlushSuspended) {
            $this->em->flush();
        }
    }
}