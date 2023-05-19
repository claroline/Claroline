<?php

namespace Claroline\PrivacyBundle\Entity;

use Claroline\AppBundle\Entity\Address;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_privacy_parameters")
 */
class Privacy
{
    use Id;
    use Uuid;
    use Address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $dpoName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $dpoEmail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $dpoPhone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $countryStorage;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $termsOfService;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isTermsOfServiceEnabled;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getDpoName(): ?string
    {
        return $this->dpoName;
    }

    public function setDpoName(?string $dpoName = null): void
    {
        $this->dpoName = $dpoName;
    }

    public function getDpoEmail(): ?string
    {
        return $this->dpoEmail;
    }

    public function setDpoEmail(?string $dpoEmail = null): void
    {
        $this->dpoEmail = $dpoEmail;
    }

    public function getDpoPhone(): ?string
    {
        return $this->dpoPhone;
    }

    public function setDpoPhone(?string $dpoPhone = null): void
    {
        $this->dpoPhone = $dpoPhone;
    }

    public function getCountryStorage(): ?string
    {
        return $this->countryStorage;
    }

    public function setCountryStorage(?string $countryStorage = null): void
    {
        $this->countryStorage = $countryStorage;
    }

    public function getTermsOfService(): ?string
    {
        return $this->termsOfService;
    }

    public function setTermsOfService(?string $termsOfService = null): void
    {
        $this->termsOfService = $termsOfService;
    }

    public function getIsTermsOfServiceEnabled(): ?bool
    {
        return $this->isTermsOfServiceEnabled;
    }

    public function setIsTermsOfServiceEnabled(?bool $isTermsOfServiceEnabled = null): void
    {
        $this->isTermsOfServiceEnabled = $isTermsOfServiceEnabled;
    }
}
