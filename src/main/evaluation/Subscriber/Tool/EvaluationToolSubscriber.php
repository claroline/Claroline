<?php

namespace Claroline\EvaluationBundle\Subscriber\Tool;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EvaluationToolSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'open_tool_desktop_evaluation' => 'onDisplayDesktop',
            'open_tool_workspace_evaluation' => 'onDisplayWorkspace',
        ];
    }

    /**
     * Displays evaluation on Desktop.
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $event->setData([
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays evaluation on Workspace.
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $event->setData([
        ]);
        $event->stopPropagation();
    }
}
