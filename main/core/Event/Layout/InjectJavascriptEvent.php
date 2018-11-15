<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Layout;

use Symfony\Component\EventDispatcher\Event;

/**
 * An event dispatched when the application UI is rendered
 * giving the chance to plugins to inject some custom javascript on any application page.
 */
class InjectJavascriptEvent extends Event
{
    private $content = '';

    public function addContent($content)
    {
        $this->content .= $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
