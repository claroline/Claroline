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

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function setMinLength(int $minLength): self
    {
        $this->minLength = $minLength;

        return $this;
    }

    public function getRequireLowercase(): bool
    {
        return $this->requireLowercase;
    }

    public function setRequireLowercase(bool $requireLowercase): self
    {
        $this->requireLowercase = $requireLowercase;

        return $this;
    }

    public function getRequireUppercase(): bool
    {
        return $this->requireUppercase;
    }

    public function setRequireUppercase(bool $requireUppercase): self
    {
        $this->requireUppercase = $requireUppercase;

        return $this;
    }

    public function getRequireSpecialChar(): bool
    {
        return $this->requireSpecialChar;
    }

    public function setRequireSpecialChar(bool $requireSpecialChar): self
    {
        $this->requireSpecialChar = $requireSpecialChar;

        return $this;
    }

    public function getRequireNumber(): bool
    {
        return $this->requireNumber;
    }

    public function setRequireNumber(bool $requireNumber): self
    {
        $this->requireNumber = $requireNumber;

        return $this;
    }

    public function getHelpMessage(): ?string
    {
        return $this->helpMessage;
    }

    public function setHelpMessage(?string $help): self
    {
        $this->helpMessage = $help;

        return $this;
    }

    public function getChangePassword(): bool
    {
        return $this->changePassword;
    }

    public function setChangePassword(bool $changePassword): self
    {
        $this->changePassword = $changePassword;

        return $this;
    }

    public function getInternalAccount(): bool
    {
        return $this->internalAccount;
    }

    public function setInternalAccount(bool $internalAccount): self
    {
        $this->internalAccount = $internalAccount;

        return $this;
    }

    public function getShowClientIp(): bool
    {
        return $this->showClientIp;
    }

    public function setShowClientIp(bool $showClientIp): self
    {
        $this->showClientIp = $showClientIp;

        return $this;
    }

    public function getRedirectAfterLoginOption(): string
    {
        return $this->redirectAfterLoginOption;
    }

    public function setRedirectAfterLoginOption(string $redirectAfterLoginOption): self
    {
        $this->redirectAfterLoginOption = $redirectAfterLoginOption;

        return $this;
    }

    public function getRedirectAfterLoginUrl(): ?string
    {
        return $this->redirectAfterLoginUrl;
    }

    public function setRedirectAfterLoginUrl(?string $redirectAfterLoginUrl): self
    {
        $this->redirectAfterLoginUrl = $redirectAfterLoginUrl;

        return $this;
    }

    public const DEFAULT_REDIRECT_OPTION = 'LAST';

    public const REDIRECT_OPTIONS = [
        'DESKTOP' => 'DESKTOP',
        'LAST' => 'LAST',
        'URL' => 'URL',
        'WORKSPACE_TAG' => 'WORKSPACE_TAG',
    ];
}
