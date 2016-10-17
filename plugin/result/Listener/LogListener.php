<?php

namespace Claroline\ResultBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * @DI\Service("claroline.result.log_listener")
 */
class LogListener
{
    private $templating;

    /**
     * LogListener constructor.
     *
     * @DI\InjectParams({
     *      "templating"      = @DI\Inject("templating")
     * })
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     *
     * @throws \Twig_Error
     *
     * @DI\Observe("create_log_list_item_resource-claroline_result-mark_added")
     * @DI\Observe("create_log_list_item_resource-claroline_result-mark_deleted")
     */
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineResultBundle:Log:log_list_item.html.twig',
            ['log' => $event->getLog()]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     *
     * @throws \Twig_Error
     *
     * @DI\Observe("create_log_details_resource-claroline_result-mark_added")
     * @DI\Observe("create_log_details_resource-claroline_result-mark_deleted")
     */
    public function onMarkEventLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineResultBundle:Log:log_details.html.twig',
            [
                'log' => $event->getLog(),
                'listItemView' => $this->templating->render(
                    'ClarolineResultBundle:Log:log_list_item.html.twig',
                    ['log' => $event->getLog()]
                ),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
