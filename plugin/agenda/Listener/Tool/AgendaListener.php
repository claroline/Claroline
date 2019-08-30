<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Listener\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 *  @DI\Service()
 */
class AgendaListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * AgendaListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @DI\Observe("open_tool_workspace_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $event->setContent([]);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
