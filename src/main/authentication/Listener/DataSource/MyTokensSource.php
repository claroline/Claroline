<?php

namespace Claroline\AuthenticationBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * List the authentication tokens of the current user.
 */
class MyTokensSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();

        $event->setData(
            $this->finder->search(ApiToken::class, $options, [Options::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
