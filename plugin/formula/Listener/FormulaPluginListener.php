<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 11/16/15
 */

namespace Icap\FormulaPluginBundle\Listener;

use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 */
class FormulaPluginListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * FormulaPluginListener constructor.
     *
     * @DI\InjectParams({
     *      "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->templating = $container->get('templating');
    }

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $content = $this->templating->render(
            'IcapFormulaPluginBundle:formula:plugin.js.html.twig'
        );

        $event->addContent($content);
    }
}
