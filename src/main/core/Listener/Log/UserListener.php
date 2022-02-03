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

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;

class UserListener
{
    public function __construct(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onUserCreate(CreateEvent $event)
    {
        $user = $event->getObject();

        $this->dispatcher->dispatch('log', 'Log\LogUserCreate', [$user]);
    }

    public function onUserPatch(PatchEvent $event)
    {
        $user = $event->getObject();
        $value = $event->getValue();
        $action = $event->getAction();

        if ($value instanceof Role) {
            if ('add' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$value, $user]);
            } elseif ('remove' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogRoleUnsubscribe', [$value, $user]);
            }
        }
    }

    public function onUserDelete(DeleteEvent $event)
    {
        $user = $event->getObject();
        $this->dispatcher->dispatch('log', 'Log\LogUserDelete', [$user]);
    }
}
