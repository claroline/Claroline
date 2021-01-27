<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\CoreBundle\Entity\Log\LogSecurity;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class SecurityEventSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::USER_LOGIN => 'logEvent',
            SecurityEvents::USER_LOGOUT => 'logEvent',
            SecurityEvents::USER_DISABLE => 'logEvent',
            SecurityEvents::USER_ENABLE => 'logEvent',
            SecurityEvents::NEW_PASSWORD => 'logEvent',
            SecurityEvents::FORGOT_PASSWORD => 'logEvent',
            SecurityEvents::ADD_ROLE => 'logEvent',
            SecurityEvents::REMOVE_ROLE => 'logEvent',
            SecurityEvents::VIEW_AS => 'logEvent',
        ];
    }

    public function logEvent($event)
    {
        $request = Request::createFromGlobals();

        $logEntry = new LogSecurity();
        $logEntry->setDetails(sprintf('Log %s: %s', $event->getEvent(), $event->getMessage()));
        $logEntry->setEvent($event->getEvent());
        $logEntry->setUser($event->getUser());
        $logEntry->setUserIp($request->getClientIp());

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
