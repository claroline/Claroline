<?php

namespace Claroline\CoreBundle\Database;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.database.writer")
 *
 * Entry point for database writing. Encapsulates the entity manager
 * and provides method to control the flush operations.
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

    /**
     * Persists a new entity and flushes if allowed.
     *
     * @param object $entity
     */
    public function create($entity)
    {
        $this->em->persist($entity);
        $this->tryFlush();
    }

    /**
     * Persists an existing entity and flushes if allowed.
     *
     * @param object $entity
     */
    public function update($entity)
    {
        $this->em->persist($entity);
        $this->tryFlush();
    }

    /**
     * Removes an entity and flushes if allowed.
     *
     * @param object $entity
     */
    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->tryFlush();
    }

    /**
     * Suspends the flush operations.
     */
    public function suspendFlush()
    {
        $this->isFlushSuspended = true;
    }

    /**
     * Forces previously suspended flush operations.
     */
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