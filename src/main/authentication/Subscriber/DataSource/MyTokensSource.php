<?php

namespace Claroline\AuthenticationBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * List the authentication tokens of the current user.
 */
class MyTokensSource implements EventSubscriberInterface
{
    public function __construct(
        private readonly FinderProvider $finder,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'data_source.my_tokens.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()?->getUser()->getUuid();

        $event->setData(
            $this->finder->search(ApiToken::class, $options, [Options::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
