<?php

namespace Claroline\CoreBundle\Persistence;

use Doctrine\Common\Persistence\ObjectManagerDecorator;
use Doctrine\Common\Persistence\ObjectManager as ObjectManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.persistence.object_manager")
 */
class ObjectManager extends ObjectManagerDecorator
{
    private $flushSuiteLevel = 0;
    private $supportsTransactions = false;
    private $hasEventManager = false;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(ObjectManagerInterface $om)
    {
        $this->wrapped = $om;
        $this->supportsTransactions
            = $this->hasEventManager
            = $om instanceof EntityManagerInterface;
    }

    /**
     * Checks if the underlying manager supports transactions.
     *
     * @return boolean
     */
    public function supportsTransactions()
    {
        return $this->supportsTransactions;
    }

    /**
     * Checks if the underlying manager has an event manager.
     *
     * @return boolean
     */
    public function hasEventManager()
    {
        return $this->hasEventManager;
    }

    /**
     * @{inheritDoc]
     *
     * This operation has no effect if one or more flush suite is active.
     */
    public function flush()
    {
        if ($this->flushSuiteLevel === 0) {
            parent::flush();
        }
    }

    /**
     * Starts a flush suite. Until the suite is ended by a call to "endFlushSuite",
     * all the flush operations are suspended. Flush suites can be nested, which means
     * that the flush takes place only when all the opened suites have been closed.
     */
    public function startFlushSuite()
    {
        ++$this->flushSuiteLevel;
    }

    /**
     * Ends a previously opened flush suite. If there is no other active suite,
     * a flush is performed.
     *
     * @throws NoFlushSuiteStartedException if no flush suite has been started
     */
    public function endFlushSuite()
    {
        if ($this->flushSuiteLevel === 0) {
            throw new NoFlushSuiteStartedException('No flush suite has been started');
        }

        --$this->flushSuiteLevel;
        $this->flush();
    }

    /**
     * Starts a transaction.
     *
     * @throws UnsupportedMethodException if the method is not supported by
     *                                    the underlying object manager
     */
    public function beginTransaction()
    {
        $this->assertIsSupported($this->supportsTransactions, __METHOD__);
        $this->wrapped->getConnection()->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @throws UnsupportedMethodException if the method is not supported by
     *                                    the underlying object manager
     */
    public function commit()
    {
        $this->assertIsSupported($this->supportsTransactions, __METHOD__);
        $this->wrapped->getConnection()->commit();
    }

    /**
     * Rollbacks a transaction.
     *
     * @throws UnsupportedMethodException if the method is not supported by
     *                                    the underlying object manager
     */
    public function rollBack()
    {
        $this->assertIsSupported($this->supportsTransactions, __METHOD__);
        $this->wrapped->getConnection()->rollBack();
    }

    /**
     * Returns the event manager.
     *
     * @throws UnsupportedMethodException if the method is not supported by
     *                                    the underlying object manager
     */
    public function getEventManager()
    {
        $this->assertIsSupported($this->hasEventManager, __METHOD__);

        return $this->wrapped->getEventManager();
    }

    /**
     * Returns an instance of a class.
     *
     * Note: this is a convenience method intended to ease unit testing, as objects
     * returned by this factory are mockable.
     *
     * @param string $class
     *
     * @return object
     *
     * @todo find a way to ensure that the class is a valid data class (e.g. by
     * using the getClassMetatadata method)
     */
    public function factory($class)
    {
        return new $class;
    }

    /**
     * Finds a set of objects by their ids.
     *
     * @param string    $objectClass
     * @param array     $ids
     *
     * @return array[object]
     *
     * @throws MissingObjectException if any of the requested objects cannot be found
     *
     * @todo make this method compatible with odm implementations
     */
    public function findByIds($class, array $ids)
    {
        $dql = 'SELECT object FROM :class object WHERE object.id IN (:ids)';
        $query = $this->wrapped->createQuery($dql);
        $query->setParameter('class', $class);
        $query->setParameter('ids', $ids);
        $objects = $query->getResult();

        if (($entityCount = count($objects)) !== ($idCount = count($ids))) {
            throw new MissingObjectException(
                "{$entityCount} out of {$idCount} ids don't match any existing object"
            );
        }

        return $objects;
    }

    /**
     * Counts objects of a given class.
     *
     * @param string $class
     *
     * @return integer
     *
     * @todo make this method compatible with odm implementations
     */
    public function count($class)
    {
        $dql = 'SELECT COUNT(object) FROM :class object';
        $query = $this->wrapped->createQuery($dql);
        $query->setParameter('class', $class);

        return $query->getSingleScalarResult();
    }

    private function assertIsSupported($isSupportedFlag, $method)
    {
        if (!$isSupportedFlag) {
            throw new UnsupportedMethodException(
                "The method '{$method}' is not supported by the underlying object manager"
            );
        }
    }
}