<?php

namespace Icap\WikiBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * LogListener.
 *
 * @DI\Service
 */
class LogListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * LogListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_create")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_move")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_update")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_delete")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_restore")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-section_remove")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-contribution_create")
     * @DI\Observe("create_log_list_item_resource-icap_wiki-configure")
     *
     * @param LogCreateDelegateViewEvent $event
     */
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->templating->render(
            'IcapWikiBundle:log:log_list_item.html.twig',
            ['log' => $event->getLog()]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_log_details_resource-icap_wiki-section_create")
     * @DI\Observe("create_log_details_resource-icap_wiki-section_move")
     * @DI\Observe("create_log_details_resource-icap_wiki-section_update")
     * @DI\Observe("create_log_details_resource-icap_wiki-section_delete")
     * @DI\Observe("create_log_details_resource-icap_wiki-section_restore")
     * @DI\Observe("create_log_details_resource-icap_wiki-section_remove")
     * @DI\Observe("create_log_details_resource-icap_wiki-contribution_create")
     * @DI\Observe("create_log_details_resource-icap_wiki-configure")
     *
     * @param LogCreateDelegateViewEvent $event
     */
    public function onSectionCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->templating->render(
            'IcapWikiBundle:log:log_details.html.twig',
            [
                'log' => $event->getLog(),
                'listItemView' => $this->templating->render(
                    'IcapWikiBundle:log:log_list_item.html.twig',
                    ['log' => $event->getLog()]
                ),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
