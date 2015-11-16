<?php

namespace Icap\FormulaPluginBundle\Listener;

use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 11/16/15
 */
class FormulaPluginListener
{
    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
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
}