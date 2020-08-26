<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

/**
 * Transfer tool.
 */
class TransferListener
{
    /** @var TransferProvider */
    private $transfer;

    /**
     * TransferListener constructor.
     *
     * @param TransferProvider $transfer
     */
    public function __construct(TransferProvider $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * Displays transfer tool.
     *
     * @param OpenToolEvent $event
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([
            'explanation' => $this->transfer->getAvailableActions('csv'),
            'samples' => $this->transfer->getSamples('csv'),
        ]);
        $event->stopPropagation();
    }
}
