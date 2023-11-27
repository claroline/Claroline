<?php

namespace Claroline\CoreBundle\Component\Log\Functional;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
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
            Crud::getEventName('update', 'post', ResourceNode::class) => ['logPublish', 10],
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
                $resource->getWorkspace(),
                $resource
            );
        }
    }
}
