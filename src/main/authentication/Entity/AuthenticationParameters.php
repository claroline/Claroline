<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_authentication_parameters')]
#[ORM\Entity]
class AuthenticationParameters
{
    use Id;

    #[ORM\Column(type: 'integer')]
    private int $minLength = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $requireLowercase = false;

    #[ORM\Column(type: 'boolean')]
    private bool $requireUppercase = false;

    #[ORM\Column(type: 'boolean')]
    private bool $requireSpecialChar = false;

    #[ORM\Column(type: 'boolean')]
    private bool $requireNumber = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $helpMessage = null;

    #[ORM\Column(type: 'boolean')]
    private bool $changePassword = false;

    #[ORM\Column(type: 'boolean')]
    private bool $internalAccount = true;

    #[ORM\Column(type: 'boolean')]
    private bool $showClientIp = false;

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function setMinLength(int $minLength): void
    {
        $this->minLength = $minLength;
    }

    public function getRequireLowercase(): bool
    {
        return $this->requireLowercase;
    }

    public function setRequireLowercase(bool $requireLowercase): void
    {
        $this->requireLowercase = $requireLowercase;
    }

    public function getRequireUppercase(): bool
    {
        return $this->requireUppercase;
    }

    public function setRequireUppercase(bool $requireUppercase): void
    {
        $this->requireUppercase = $requireUppercase;
    }

    public function getRequireSpecialChar(): bool
    {
        return $this->requireSpecialChar;
    }

    public function setRequireSpecialChar(bool $requireSpecialChar): void
    {
        $this->requireSpecialChar = $requireSpecialChar;
    }

    public function getRequireNumber(): bool
    {
        return $this->requireNumber;
    }

    public function setRequireNumber(bool $requireNumber): void
    {
        $this->requireNumber = $requireNumber;
    }

    public function getHelpMessage(): ?string
    {
        return $this->helpMessage;
    }

    public function setHelpMessage(?string $help): void
    {
        $this->helpMessage = $help;
    }

    public function getChangePassword(): bool
    {
        return $this->changePassword;
    }

    public function setChangePassword(bool $changePassword): void
    {
        $this->changePassword = $changePassword;
    }

    public function getInternalAccount(): bool
    {
        return $this->internalAccount;
    }

    public function setInternalAccount(bool $internalAccount): void
    {
        $this->internalAccount = $internalAccount;
    }

    public function getShowClientIp(): bool
    {
        return $this->showClientIp;
    }

    public function setShowClientIp(bool $showClientIp): void
    {
        $this->showClientIp = $showClientIp;
    }
}
