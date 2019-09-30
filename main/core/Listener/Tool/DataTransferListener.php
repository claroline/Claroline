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

/**
 * Home tool.
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
