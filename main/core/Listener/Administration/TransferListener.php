<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transfer tool.
 *
 * @DI\Service()
 */
class TransferListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var TransferProvider */
    private $transfer;

    /**
     * TransferListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "transfer"   = @DI\Inject("claroline.api.transfer")
     * })
     *
     * @param TwigEngine       $templating
     * @param TransferProvider $transfer
     */
    public function __construct(
        TwigEngine $templating,
        TransferProvider $transfer)
    {
        $this->templating = $templating;
        $this->transfer = $transfer;
    }

    /**
     * Displays transfer tool.
     *
     * @DI\Observe("administration_tool_data_transfer")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:transfer.html.twig', [
                'explanation' => $this->transfer->getAvailableActions('csv'),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
