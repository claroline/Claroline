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
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

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
     * @param OpenToolEvent $event
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $options = [Options::WORKSPACE_IMPORT];
        $extra = [
            'workspace' => $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]),
        ];

        $event->setData([
            'explanation' => $this->transfer->getAvailableActions('csv', $options, $extra),
            'samples' => $this->transfer->getSamples('csv', $options, $extra),
        ]);
        $event->stopPropagation();
    }
}
