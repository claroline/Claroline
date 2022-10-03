<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ImagePlayerBundle\Listener\File\Type;

use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;

/**
 * Integrates Image files into Claroline.
 */
class ImageListener
{
    public function onLoad(LoadFileEvent $event)
    {
        // setting empty data let the dispatcher know there is
        // a player for image but it doesn't require any additional data
        // without it, the dispatcher will try to find a player for "application/*"
        $event->setData([]);
        $event->stopPropagation();
    }
}
