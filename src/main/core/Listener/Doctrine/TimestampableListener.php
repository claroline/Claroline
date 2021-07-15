<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Doctrine;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\Persistence\NotifyPropertyChanged;
use Gedmo\Timestampable\TimestampableListener as BaseListener;

/**
 * This listener overrides its parent's original behaviour in two ways:.
 *
 * - it performs a special check for ResourceNode entities, avoiding
 *   timestamp changes if the modified properties are only related to
 *   the node position (attributes "previous" and "next")
 *
 * - it allows to set an arbitrary time as a the reference timestamp
 *   (needed during synchronization of older data)
 */
class TimestampableListener extends BaseListener
{
    private $forcedTime;

    /**
     * Forces this listener to set a specific time on subsequent
     * timestampable entities.
     */
    public function forceTime(\DateTime $time)
    {
        $this->forcedTime = $time;
    }

    protected function updateField($object, $ea, $meta, $field)
    {
        if ($this->isUpdateNeeded($object, $ea)) {
            $property = $meta->getReflectionProperty($field);
            $oldValue = $property->getValue($object);
            $newValue = $this->forcedTime ?: $ea->getDateValue($meta, $field);
            $property->setValue($object, $newValue);

            if ($object instanceof NotifyPropertyChanged) {
                $uow = $ea->getObjectManager()->getUnitOfWork();
                $uow->propertyChanged($object, $field, $oldValue, $newValue);
            }
        }
    }

    private function isUpdateNeeded($object, $ea)
    {
        if ($object instanceof ResourceNode) {
            $uow = $ea->getObjectManager()->getUnitOfWork();
            $changeSet = $uow->getEntityChangeSet($object);

            $count = count($changeSet);
            if (0 !== $count && $count < 3) {
                $hasSignificantChange = false;

                foreach (array_keys($changeSet) as $changedField) {
                    if ('next' !== $changedField && 'previous' !== $changedField) {
                        $hasSignificantChange = true;
                        break;
                    }
                }

                if (!$hasSignificantChange) {
                    return false;
                }
            }
        }

        return true;
    }
}
