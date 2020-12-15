<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Log;

use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;

class RoleListener
{
    public function __construct(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onRolePatch(PatchEvent $event)
    {
        $role = $event->getObject();
        $value = $event->getValue();
        $action = $event->getAction();

        if ('add' === $action) {
            $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$role, $value]);
        } elseif ('remove' === $action) {
            $this->dispatcher->dispatch('log', 'Log\LogRoleUnsubscribe', [$role, $value]);
        }
    }
}
