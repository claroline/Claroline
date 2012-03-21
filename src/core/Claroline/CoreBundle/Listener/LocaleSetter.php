<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleSetter
{
    protected $session;
    protected $configHandler;
    
    public function __construct($session, $configHandler)
    {
        $this->session = $session;
        $this->configHandler = $configHandler;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->session->setLocale($this->configHandler->getParameter('locale_language'));      
    }
}