<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Listener;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class LayoutListener
{
    /**
     * LayoutListener constructor.
     *
     * @param EngineInterface    $templating
     */
    public function __construct(
        EngineInterface $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @param InjectJavascriptEvent $event
     *
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('ClarolineOpenBadgeBundle::javascripts.html.twig')
        );
    }
}
