<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\LogUserLoginEvent;

class AuthenticationSuccessListener extends ContainerAware
{
    public function onAuthenticationSuccess()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $log = new LogUserLoginEvent($user);
        $this->container->get('event_dispatcher')->dispatch('log', $log);
    }
}