<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\CoreBundle\Entity\Log\SecurityLog;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityEventSubscriber implements EventSubscriberInterface
{
    private $em;
    private $client;
    private $security;
    private $requestStack;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $client,
        Security $security,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->client = $client;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
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
            SecurityEvents::VALIDATE_EMAIL => 'logEvent',
            SecurityEvents::AUTHENTICATION_FAILURE => 'logEvent',
        ];
    }

    public function logEvent(Event $event, string $eventName): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $logEntry = new SecurityLog();
        $logEntry->setDetails(sprintf($event->getMessage($this->translator)));
        $logEntry->setEvent($eventName);
        $logEntry->setTarget($event->getUser());
        $logEntry->setDoer($this->security->getUser());

        //Get infos from ip address
        $response = json_decode($this->client->request('GET', 'http://ip-api.com/json/'.$request->getClientIp()), true);
        $logEntry->setDoerIp(IpUtils::anonymize($request->getClientIp()));
        $logEntry->setCountry($response['country'] ?? 'no country');
        $logEntry->setCity($response['city'] ?? 'no city');

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
