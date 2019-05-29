<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * Home tool.
 *
 * @DI\Service()
 */
class DataTransferListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "transfer"   = @DI\Inject("claroline.api.transfer")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(TwigEngine $templating, TransferProvider $transfer, SerializerProvider $serializer)
    {
        $this->templating = $templating;
        $this->transfer = $transfer;
        $this->serializer = $serializer;
    }

    /**
     * Displays home on Workspace.
     *
     * @DI\Observe("open_tool_workspace_data_transfer")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $explanations = $this->transfer->getAvailableActions('csv', [Options::WORKSPACE_IMPORT], [
          'workspace' => $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]),
        ]);

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:data-transfer.html.twig', [
              'context' => [
                  'type' => 'workspace',
                  'data' => $this->serializer->serialize($workspace),
              ],
              'workspace' => $workspace,
              'explanation' => $explanations,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
