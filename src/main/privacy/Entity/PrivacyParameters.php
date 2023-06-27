<?php

namespace Claroline\PrivacyBundle\Entity;

use Claroline\AppBundle\Entity\Address;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_privacy_parameters")
 */
class PrivacyParameters
{
    use Id;
    use Address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dpoName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dpoEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dpoPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $countryStorage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $termsOfService;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $termsOfServiceEnabled;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     */
    protected $publicationDate;

    //relation vers template par defaut trait use template juste pour definition de la relation

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

    public function isTermsOfServiceEnabled(): ?bool
    {
        return $this->termsOfServiceEnabled;
    }

    public function setTermsOfServiceEnabled(?bool $termsOfServiceEnabled = null): void
    {
        $this->termsOfServiceEnabled = $termsOfServiceEnabled;
    }

    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTime $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }
}
