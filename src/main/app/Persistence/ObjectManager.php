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

use Doctrine\ORM\Decorator\EntityManagerDecorator;

class ObjectManager extends EntityManagerDecorator
{
    private int $flushSuiteLevel = 0;

    /**
     * {@inheritdoc}
     *
     * This operation has no effect if one or more flush suite is active.
     */
    public function flush($entity = null): void
    {
        if (0 === $this->flushSuiteLevel) {
            parent::flush($entity);
        }
    }

    /**
     * Starts a flush suite. Until the suite is ended by a call to "endFlushSuite",
     * all the flush operations are suspended. Flush suites can be nested, which means
     * that the flush takes place only when all the opened suites have been closed.
     */
    public function startFlushSuite(): void
    {
        ++$this->flushSuiteLevel;
    }

    /**
     * Ends a previously opened flush suite. If there is no other active suite,
     * a flush is performed.
     *
     * @throws NoFlushSuiteStartedException if no flush suite has been started
     */
    public function endFlushSuite(): void
    {
        if (0 === $this->flushSuiteLevel) {
            throw new NoFlushSuiteStartedException('No flush suite has been started');
        }

        --$this->flushSuiteLevel;
        $this->flush();
    }

    /**
     * Forces a flush.
     */
    public function forceFlush(): void
    {
        parent::flush();
    }

    public function find($className, mixed $id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->wrapped->getRepository($className)->findOneBy(
            !is_numeric($id) && property_exists($className, 'uuid') ?
                ['uuid' => $id] :
                ['id' => $id]
        );
    }

    /**
     * Fetch an object from database according to the class and the id/uuid of the data.
     *
     * @return object|null
     */
    public function getObject(array $data, string $class, array $identifiers = []): mixed
    {
        $object = null;

        // try to retrieve object with its id
        if (isset($data['id']) || isset($data['uuid'])) {
            if (isset($data['uuid'])) {
                $object = $this->getRepository($class)->findOneBy(['uuid' => $data['uuid']]);
            } else {
                $object = !is_numeric($data['id']) && property_exists($class, 'uuid') ?
                $this->getRepository($class)->findOneBy(['uuid' => $data['id']]) :
                $this->getRepository($class)->findOneBy(['id' => $data['id']]);
            }
        }

        // try other object identifiers if any
        if (empty($object) && !empty($identifiers)) {
            foreach (array_keys($data) as $property) {
                if (in_array($property, $identifiers) && !$object) {
                    $object = $this->getRepository($class)->findOneBy([$property => $data[$property]]);

                    if ($object) {
                        break;
                    }
                }
            }
        }

        return $object;
    }
}
