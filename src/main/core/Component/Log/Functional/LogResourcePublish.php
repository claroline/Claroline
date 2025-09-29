<?php

namespace Claroline\CoreBundle\Component\Log\Functional;

use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourcePublish extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'resource.publish';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, ResourceNode::class) => ['logPublish', -25],
        ];
    }

    public function logPublish(UpdateEvent $event): void
    {
        $resource = $event->getObject();
        $old = $event->getOldData();

        if ($old['meta']['published'] !== $resource->isPublished() && $resource->isPublished()) {
            $this->log(
                $this->getTranslator()->trans('resource.publish_message', [
                    '%resource%' => $resource->getName(),
                ], 'log'),
                $resource
            );
        }
    }

    protected function getContextId(?object $object = null): ?string
    {
        return $object->getWorkspace()?->getContextIdentifier();
    }
}
