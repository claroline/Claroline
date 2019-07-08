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
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 *  @DI\Service()
 */
class AgendaListener
{
    /** @var TwigEngine */
    private $templating;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * AgendaListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param TwigEngine         $templating
     * @param SerializerProvider $serializer
     */
    public function __construct(
        TwigEngine $templating,
        SerializerProvider $serializer
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
    }

    /**
     * @DI\Observe("open_tool_workspace_agenda_")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $content = $this->templating->render(
            'ClarolineAgendaBundle:tool:agenda.html.twig', [
                'workspace' => $workspace,
                'context' => [
                    'type' => Tool::WORKSPACE,
                    'data' => $this->serializer->serialize($workspace),
                ],
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_agenda_")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
