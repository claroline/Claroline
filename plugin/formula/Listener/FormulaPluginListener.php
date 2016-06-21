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
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 */
class FormulaPluginListener extends ContainerAware
{
    private $templating;

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     *
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $content = $this->templating->render(
            'IcapFormulaPluginBundle:Formula:plugin.js.html.twig',
            array()
        );
        $event->addContent($content);
    }

    /**
     * @DI\InjectParams({
     *      "container"   = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->templating = $container->get('templating');
    }
}
