<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\CrudEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Helper\LinkHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractOperationalLog implements EventSubscriberInterface, ComponentInterface
{
    use LogComponentTrait;

    private SerializerProvider $serializer;

    abstract protected static function getEntityClass(): string;

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', static::getEntityClass()) => ['logCreate', -25],
            Crud::getEventName('update', 'post', static::getEntityClass()) => ['logUpdate', -25],
            Crud::getEventName('copy', 'post', static::getEntityClass()) => ['logCopy', -25],
            Crud::getEventName('patch', 'post', static::getEntityClass()) => ['logPatch', -25],
            Crud::getEventName('delete', 'post', static::getEntityClass()) => ['logDelete', -25],
        ];
    }

    public function setSerializer(SerializerProvider $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function logCreate(CreateEvent $event): void
    {
        $this->log('create', $this->getMessageFromEvent($event, 'create'), $event->getObject()->getUuid());
    }

    public function logUpdate(UpdateEvent $event): void
    {
        // we don't directly use data from event because it can contain only partial data.
        // maybe we should do it directly in the CRUD, but we only exploit it in the logs for now
        // and not all the CrudEntity are logged
        $newData = $this->serializer->serialize($event->getObject());
        $changeSet = $this->getUpdateDiff($event->getOldData(), $newData);
        if (count($changeSet) > 0) {
            $this->log('update', $this->getMessageFromEvent($event, 'update'), $event->getObject()->getUuid());
        }
    }

    public function logCopy(CopyEvent $event): void
    {
        $this->log('copy', $this->getMessageFromEvent($event, 'copy'), $event->getObject()->getUuid());
    }

    public function logPatch(PatchEvent $event): void
    {
    }

    public function logDelete(DeleteEvent $event): void
    {
        $this->log('delete', $this->getMessageFromEvent($event, 'delete'), $event->getObject()->getUuid());
    }

    /**
     * Get the log message from the dispatched CrudEvent.
     * Override this method to specialize your log messages if needed.
     */
    protected function getMessageFromEvent(CrudEvent $event, string $action): string
    {
        $translationKey = static::getName().'.'.$action.'_message';

        switch (get_class($event)) {
            /*case CreateEvent::class:
                return $this->getTranslator()->trans($translationKey, [
                    '%object%' => LinkHelper::link($this->getObjectName($event->getObject()), $this->getObjectPath($event->getObject())),
                ], 'log');
            case UpdateEvent::class:
                return $this->getTranslator()->trans($translationKey, [
                    '%object%' => LinkHelper::link($this->getObjectName($event->getObject()), $this->getObjectPath($event->getObject()))
                ], 'log');*/
            case CopyEvent::class:
                return $this->getTranslator()->trans($translationKey, [
                    '%object%' => LinkHelper::link($this->getObjectName($event->getObject()), $this->getObjectPath($event->getObject())),
                    '%copy%' => LinkHelper::link($this->getObjectName($event->getCopy()), $this->getObjectPath($event->getCopy())),
                ], 'log');
            default:
                return $this->getTranslator()->trans($translationKey, [
                    '%object%' => LinkHelper::link($this->getObjectName($event->getObject()), $this->getObjectPath($event->getObject())),
                ], 'log');
        }
    }

    protected function getObjectName(mixed $object): string
    {
        return $object->getName();
    }

    protected function getObjectPath(mixed $object): ?string
    {
        return null;
    }

    /**
     * Utility method to create a new log.
     *
     * Note :
     *     - If $doer is not set, the method will try to retrieve it from the TokenStorage.
     *     - We allow to set the doer through params for some edge cases where the doer is not the current user.
     */
    protected function log(string $action, string $message, string $objectId, array $changeset = [], User $doer = null): void
    {
        if (empty($doer)) {
            $doer = $this->getCurrentUser();
        }

        $this->logManager->logOperational(static::getName().'.'.$action, $message, $doer, static::getEntityClass(), $objectId, $changeset);
    }

    private function getUpdateDiff(array $old, mixed $new): array
    {
        $result = [];
        foreach ($old as $key => $val) {
            if (isset($new[$key])) {
                if (is_array($val) && $new[$key]) {
                    $result[$key] = $this->getUpdateDiff($val, $new[$key]);
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }
}
