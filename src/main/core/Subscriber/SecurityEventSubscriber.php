<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\CoreBundle\Entity\Log\LogSecurity;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecurityEventSubscriber implements EventSubscriberInterface
{
    private $em;
    private $client;
    private $security;
    private $requestStack;

    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $client,
        Security $security,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->client = $client;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
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

    public function logEvent(Event $event, string $eventName): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $logEntry = new LogSecurity();
        $logEntry->setDetails(sprintf('Log %s: %s', $eventName, $event->getMessage()));
        $logEntry->setEvent($eventName);
        $logEntry->setTarget($event->getUser());
        $logEntry->setDoer($this->security->getUser());
        $logEntry->setDoerIp($request->getClientIp());

        //Get infos from ip address
        $response = json_decode($this->client->request('GET', 'http://ip-api.com/json/'.$request->getClientIp()), true);
        $logEntry->setCountry($response['country']);
        $logEntry->setCity($response['city']);

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
