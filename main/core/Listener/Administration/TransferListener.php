<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Transfer tool.
 *
 * @DI\Service()
 */
class TransferListener
{
    /** @var TransferProvider */
    private $transfer;

    /**
     * TransferListener constructor.
     *
     * @DI\InjectParams({
     *     "transfer" = @DI\Inject("claroline.api.transfer")
     * })
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
     * @DI\Observe("administration_tool_transfer")
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
