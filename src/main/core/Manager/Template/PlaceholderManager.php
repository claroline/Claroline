<?php

namespace Claroline\CoreBundle\Manager\Template;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlaceholderManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PlatformConfigurationHandler */
    private $config;

    /**
     * PlaceholderManager constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
    }

    public function getAvailablePlaceholders()
    {
        return [
            'platform_name',
            'platform_url',
            'date',
            'datetime',
            'current_user_id',
            'current_user_username',
            'current_user_first_name',
            'current_user_last_name',
        ];
    }

    /**
     * @return string
     */
    public function replacePlaceholders(string $text, array $customPlaceholders = [])
    {
        $now = new \DateTime();

        /** @var User|null $currentUser */
        $currentUser = null;
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
        }

        $placeholders = [
            '%platform_name%' => $this->config->getParameter('display.name'),
            '%platform_url%' => $this->config->getParameter('internet.platform_url'),
            '%date%' => $now->format('Y-m-d'), // should be in locale format
            '%datetime%' => $now->format('Y-m-d H:i:s'), // should be in locale format
            '%current_user_id%' => $currentUser ? $currentUser->getUuid() : null,
            '%current_user_username%' => $currentUser ? $currentUser->getUsername() : null,
            '%current_user_first_name%' => $currentUser ? $currentUser->getFirstName() : null,
            '%current_user_last_name%' => $currentUser ? $currentUser->getLastName() : null,
        ];

        foreach ($customPlaceholders as $key => $value) {
            $placeholders['%'.$key.'%'] = $value;
        }

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }
}
