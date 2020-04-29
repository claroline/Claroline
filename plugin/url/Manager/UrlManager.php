<?php

namespace HeVinci\UrlBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UrlManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * UrlManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getAvailablePlaceholders()
    {
        return [
            'current_user_id',
            'current_user_username',
            'current_user_first_name',
            'current_user_last_name',
        ];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function replacePlaceholders(string $url)
    {
        /** @var User|null $currentUser */
        $currentUser = null;
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
        }

        $placeholders = [
            '%current_user_id%' => $currentUser ? $currentUser->getUuid() : null,
            '%current_user_username%' => $currentUser ? $currentUser->getUsername() : null,
            '%current_user_first_name%' => $currentUser ? $currentUser->getFirstName() : null,
            '%current_user_last_name%' => $currentUser ? $currentUser->getLastName() : null,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $url);
    }
}
