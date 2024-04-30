<?php

namespace Claroline\CoreBundle\Component\Log\Functional;

use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourceOpen extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'resource.open';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::OPEN => ['logOpen', -25],
        ];
    }

    public function logOpen(LoadResourceEvent $openEvent): void
    {
        $openedResource = $openEvent->getResourceNode();

        $this->log(
            $this->getTranslator()->trans('resource.open_message', [
                '%resource%' => $openedResource->getName(),
            ], 'log'),
            $openedResource->getWorkspace(),
            $openedResource
        );
    }
}
