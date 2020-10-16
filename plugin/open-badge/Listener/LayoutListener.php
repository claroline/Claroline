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
use Twig\Environment;

class LayoutListener
{
    /**
     * LayoutListener constructor.
     */
    public function __construct(
        Environment $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('@ClarolineOpenBadge/javascripts.html.twig')
        );
    }
}
