<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;

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
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $event->setData([
            'explanation' => $this->transfer->getAvailableActions('csv'),
        ]);
        $event->stopPropagation();
    }
}
