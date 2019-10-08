<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;

class TagsListener
{
    /**
     * @param OpenAdministrationToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
