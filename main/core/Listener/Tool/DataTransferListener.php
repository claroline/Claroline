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

/**
 * Home tool.
 *
 * @DI\Service()
 */
class DataTransferListener
{
    /** @var TransferProvider */
    private $transfer;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * DataTransferListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "transfer"   = @DI\Inject("claroline.api.transfer")
     * })
     *
     * @param TransferProvider   $transfer
     * @param SerializerProvider $serializer
     */
    public function __construct(TransferProvider $transfer, SerializerProvider $serializer)
    {
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

        $event->setData([
          'explanation' => $explanations,
        ]);
        $event->stopPropagation();
    }
}
