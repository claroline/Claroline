<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
}
