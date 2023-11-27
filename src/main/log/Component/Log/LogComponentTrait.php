<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Manager\LogManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait LogComponentTrait
{
    private TokenStorageInterface $tokenStorage;
    private TranslatorInterface $translator;
    private LogManager $logManager;

    /**
     * @internal only used by DI
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @internal only used by DI
     */
    public function setLogManager(LogManager $logManager): void
    {
        $this->logManager = $logManager;
    }

    /**
     * @internal only used by DI
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    protected function getCurrentUser(): ?User
    {
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            return $this->tokenStorage->getToken()->getUser();
        }

        return null;
    }
}
