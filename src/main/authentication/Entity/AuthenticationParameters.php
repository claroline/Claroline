<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_authentication_parameters")
 */
class AuthenticationParameters
{
    use Id;
    public const DEFAULT_REDIRECT_OPTION = 'LAST';

    public const REDIRECT_OPTIONS = [
        'DESKTOP' => 'DESKTOP',
        'LAST' => 'LAST',
        'URL' => 'URL',
        'WORKSPACE_TAG' => 'WORKSPACE_TAG',
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private int $minLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $requireLowercase;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $requireUppercase;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $requireSpecialChar;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $requireNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $helpMessage;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $changePassword;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $internalAccount;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $showClientIp;

    /**
     * @ORM\Column(type="string")
     */
    private string $redirectAfterLoginOption;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $redirectAfterLoginUrl;

    public function __construct()
    {
        $this->minLength = 0;
        $this->requireLowercase = false;
        $this->requireUppercase = false;
        $this->requireSpecialChar = false;
        $this->requireNumber = false;

        $this->helpMessage = null;
        $this->changePassword = true;
        $this->internalAccount = true;
        $this->showClientIp = false;
        $this->redirectAfterLoginOption = self::DEFAULT_REDIRECT_OPTION;
        $this->redirectAfterLoginUrl = null;
    }

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

    public function getRedirectAfterLoginOption(): string
    {
        return $this->redirectAfterLoginOption;
    }

    public function setRedirectAfterLoginOption(string $redirectAfterLoginOption): void
    {
        $this->redirectAfterLoginOption = $redirectAfterLoginOption;
    }

    public function getRedirectAfterLoginUrl(): ?string
    {
        return $this->redirectAfterLoginUrl;
    }

    public function setRedirectAfterLoginUrl(?string $redirectAfterLoginUrl): void
    {
        $this->redirectAfterLoginUrl = $redirectAfterLoginUrl;
    }
}
