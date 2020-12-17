<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class ParametersListener
{
    /**
     * @param OpenToolEvent $event
     */
    public function onDisplayDesktopParameters(OpenToolEvent $event)
    {
        $event->setData([]);
    }
}
