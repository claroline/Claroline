<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Log;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class LogController
{
    /** @var TwigEngine */
    private $templating;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * LogController constructor.
     *
     * @DI\InjectParams({
     *     "templating"      = @DI\Inject("templating"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param TwigEngine               $templating
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        TwigEngine $templating,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->templating = $templating;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route("/view_details/{logId}", name="claro_log_view_details", options={"expose"=true})
     * @EXT\ParamConverter(
     *      "log",
     *      class="ClarolineCoreBundle:Log\Log",
     *      options={"id" = "logId", "strictId" = true},
     *      converter="strict_id"
     * )
     *
     * @param Log $log
     *
     * @return Response
     */
    public function viewDetailsAction(Log $log)
    {
        $eventLogName = 'create_log_details_'.$log->getAction();

        if ($this->eventDispatcher->hasListeners($eventLogName)) {
            /** @var LogCreateDelegateViewEvent $event */
            $event = $this->eventDispatcher->dispatch(
                $eventLogName,
                new LogCreateDelegateViewEvent($log)
            );

            return new Response($event->getResponseContent());
        }

        return $this->templating->render('ClarolineCoreBundle:log:view_details.html.twig', [
            'log' => $log,
        ]);
    }
}
