<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Persistence;

use Claroline\AppBundle\Log\LoggableTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ObjectManager as ObjectManagerInterface;
use Doctrine\Persistence\ObjectManagerDecorator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

class ObjectManager extends ObjectManagerDecorator implements LoggerAwareInterface
{
    use LoggableTrait;

    private $flushSuiteLevel = 0;
    private $supportsTransactions = false;
    private $hasEventManager = false;
    private $hasUnitOfWork = false;
    private $activateLog = false;
    private $allowForceFlush = true;
    private $showFlushLevel = false;

    /**
     * ObjectManager constructor.
     */
    public function __construct(ObjectManagerInterface $om)
    {
        $this->wrapped = $om;
        $this->supportsTransactions
            = $this->hasEventManager
            = $this->hasUnitOfWork
            = $om instanceof EntityManagerInterface;
    }

    /**
     * Checks if the underlying manager supports transactions.
     *
     * @return bool
     */
    public function supportsTransactions()
    {
        return $this->supportsTransactions;
    }

    /**
     * Checks if the underlying manager has an event manager.
     *
     * @return bool
     */
    public function hasEventManager()
    {
        return $this->hasEventManager;
    }

    /**
     * Checks if the underlying manager has an unit of work.
     *
     * @return bool
     */
    public function hasUnitOfWork()
    {
        return $this->hasUnitOfWork;
    }

    /**
     * {@inheritdoc}
     *
     * This operation has no effect if one or more flush suite is active.
     */
    public function flush()
    {
        if (0 === $this->flushSuiteLevel) {
            if ($this->activateLog) {
                $this->log('Flush was started.');
            }
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
        if ($this->activateLog && $this->showFlushLevel) {
            $this->logFlushLevel();
        }
    }

    /**
     * Ends a previously opened flush suite. If there is no other active suite,
     * a flush is performed.
     *
     * @throws NoFlushSuiteStartedException if no flush suite has been started
     */
    public function endFlushSuite()
    {
        if (0 === $this->flushSuiteLevel) {
            throw new NoFlushSuiteStartedException('No flush suite has been started');
        }

        --$this->flushSuiteLevel;
        $this->flush();
        if ($this->activateLog && $this->showFlushLevel) {
            $this->logFlushLevel();
        }
    }

    /**
     * Forces a flush.
     */
    public function forceFlush()
    {
        if ($this->allowForceFlush) {
            if ($this->activateLog) {
                $this->log('Flush was forced for level '.$this->flushSuiteLevel.'.');
            }
            parent::flush();
        }
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->wrapped->createQueryBuilder();
    }

    /**
     * @param string $dql
     *
     * @return QueryBuilder
     */
    public function createQuery($dql = '')
    {
        return $this->wrapped->createQuery($dql);
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
     * Returns the unit of work.
     *
     * @return UnitOfWork
     *
     * @throws UnsupportedMethodException if the method is not supported by
     *                                    the underlying object manager
     */
    public function getUnitOfWork()
    {
        $this->assertIsSupported($this->hasUnitOfWork, __METHOD__);

        return $this->wrapped->getUnitOfWork();
    }

    /**
     * Finds a set of objects by their ids.
     *
     * @param $class
     * @param bool $orderStrict keep the same order as ids array
     *
     * @return array [object]
     *
     * @throws MissingObjectException if any of the requested objects cannot be found
     *
     * @internal param string $objectClass
     *
     * @todo make this method compatible with odm implementations
     */
    public function findByIds($class, array $ids, $orderStrict = false)
    {
        return $this->findList($class, 'id', $ids, $orderStrict);
    }

    /**
     * Finds a set of objects.
     *
     * @param $class
     * @param $property
     * @param bool $orderStrict keep the same order as ids array
     *
     * @return array [object]
     *
     * @throws MissingObjectException if any of the requested objects cannot be found
     *
     * @internal param string $objectClass
     *
     * @todo make this method compatible with odm implementations
     */
    public function findList($class, $property, array $list = [], $orderStrict = false)
    {
        if (0 === count($list)) {
            return [];
        }

        $dql = "SELECT object FROM {$class} object WHERE object.{$property} IN (:list)";
        $query = $this->wrapped->createQuery($dql);
        $query->setParameter('list', $list);
        $objects = $query->getResult();

        if (($entityCount = count($objects)) !== ($idCount = count($list))) {
            $this->log("{$entityCount} out of {$idCount} ids don't match any existing object", LogLevel::WARNING);
        }

        if ($orderStrict) {
            // Sort objects to have the same order as given $ids array
            $sortIds = array_flip($list);
            usort($objects, function ($a, $b) use ($sortIds) {
                return $sortIds[$a->getId()] - $sortIds[$b->getId()];
            });
        }

        return $objects;
    }

    /**
     * Counts objects of a given class.
     *
     * @param string $class
     *
     * @return int
     *
     * @todo make this method compatible with odm implementations
     */
    public function count($class)
    {
        $dql = "SELECT COUNT(object) FROM {$class} object";
        $query = $this->wrapped->createQuery($dql);

        return (int) $query->getSingleScalarResult();
    }

    private function assertIsSupported($isSupportedFlag, $method)
    {
        if (!$isSupportedFlag) {
            throw new UnsupportedMethodException("The method '{$method}' is not supported by the underlying object manager");
        }
    }

    /**
     * Please be carefull if you remove the force flush...
     */
    public function allowForceFlush($bool)
    {
        $this->allowForceFlush = $bool;
    }

    public function activateLog()
    {
        $this->activateLog = true;

        return $this;
    }

    public function disableLog()
    {
        $this->activateLog = false;

        return $this;
    }

    public function showFlushLevel()
    {
        $this->showFlushLevel = true;
    }

    public function hideFlushLevel()
    {
        $this->showFlushLevel = false;
    }

    private function logFlushLevel()
    {
        $stack = debug_backtrace();

        foreach ($stack as $call) {
            if ('endFlushSuite' === $call['function'] || 'startFlushSuite' === $call['function']) {
                if (method_exists($this, 'log')) {
                    $this->log('Function "'.$call['function'].'" was called from file '.$call['file'].' on line '.$call['line'].'.', LogLevel::DEBUG);
                } else {
                    echo 'Function "'.$call['function'].'" was called from file '.$call['file'].' on line '.$call['line'].'.';
                }
            }
        }

        if (method_exists($this, 'log')) {
            $this->log('Flush level: '.$this->flushSuiteLevel.'.');
        } else {
        }
    }

    public function save($object, $options = [], $log = true)
    {
        $this->persist($object);

        if ($log) {
            //maybe log some stuff according to the options
        }

        $this->flush();
    }

    /**
     * @param string     $class
     * @param string|int $id
     *
     * @return object|null
     */
    public function find($class, $id)
    {
        return $this->wrapped->getRepository($class)->findOneBy(
            !is_numeric($id) && property_exists($class, 'uuid') ?
                ['uuid' => $id] :
                ['id' => $id]
        );
    }

    /**
     * Fetch an object from database according to the class and the id/uuid of the data.
     *
     * @param string $class
     *
     * @return object|null
     */
    public function getObject(array $data, $class, array $identifiers = [])
    {
        $object = null;

        if (isset($data['id']) || isset($data['uuid'])) {
            if (isset($data['uuid'])) {
                $object = $this->getRepository($class)->findOneBy(['uuid' => $data['uuid']]);
            } else {
                $object = !is_numeric($data['id']) && property_exists($class, 'uuid') ?
                $this->getRepository($class)->findOneBy(['uuid' => $data['id']]) :
                $this->getRepository($class)->findOneBy(['id' => $data['id']]);
            }

            return $object;
        }

        foreach (array_keys($data) as $property) {
            if (in_array($property, $identifiers) && !$object) {
                $object = $this->getRepository($class)->findOneBy([$property => $data[$property]]);

                if ($object) {
                    return $object;
                }
            }
        }

        return $object;
    }

    public function ignoreForeignKeys()
    {
        $conn = $this->wrapped->getConnection();
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    public function restoreForeignKeys()
    {
        $conn = $this->wrapped->getConnection();
        $sql = 'SET FOREIGN_KEY_CHECKS=1;';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}
