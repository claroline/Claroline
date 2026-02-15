<?php

namespace Claroline\PrivacyBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\TemplateBundle\Entity\Template;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_privacy_parameters')]
#[ORM\Entity]
class PrivacyParameters
{
    use Id;

    #[ORM\Column(name: 'country_storage', type: Types::STRING, nullable: true)]
    private ?string $countryStorage = null;

    #[ORM\Column(name: 'dpo_name', type: Types::STRING, nullable: true)]
    private ?string $dpoName = null;

    #[ORM\Column(name: 'dpo_email', type: Types::STRING, nullable: true)]
    private ?string $dpoEmail = null;

    #[ORM\Column(name: 'dpo_phone', type: Types::STRING, nullable: true)]
    private ?string $dpoPhone = null;

    #[ORM\Column(name: 'dpo_address_street1', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressStreet1 = null;

    #[ORM\Column(name: 'dpo_address_street2', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressStreet2 = null;

    #[ORM\Column(name: 'dpo_address_postal_code', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressPostalCode = null;

    #[ORM\Column(name: 'dpo_address_city', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressCity = null;

    #[ORM\Column(name: 'dpo_address_state', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressState = null;

    #[ORM\Column(name: 'dpo_address_country', type: Types::STRING, nullable: true)]
    private ?string $dpoAddressCountry = null;

    #[ORM\Column(name: 'tos_enabled', type: Types::BOOLEAN)]
    private bool $tosEnabled = false;

    #[ORM\JoinColumn(name: 'tos_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $tosTemplate = null;

    #[ORM\JoinColumn(name: 'privacy_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $privacyTemplate = null;

    public function getCountryStorage(): ?string
    {
        return $this->countryStorage;
    }

    public function setCountryStorage(?string $countryStorage): void
    {
        $this->countryStorage = $countryStorage;
    }

    public function getDpoName(): ?string
    {
        return $this->dpoName;
    }

    public function setDpoName(?string $dpoName): void
    {
        $this->dpoName = $dpoName;
    }

    public function getDpoEmail(): ?string
    {
        return $this->dpoEmail;
    }

    public function setDpoEmail(?string $dpoEmail): void
    {
        $this->dpoEmail = $dpoEmail;
    }

    public function getDpoPhone(): ?string
    {
        return $this->dpoPhone;
    }

    public function setDpoPhone(?string $dpoPhone): void
    {
        $this->dpoPhone = $dpoPhone;
    }

    public function getDpoAddressStreet1(): ?string
    {
        return $this->dpoAddressStreet1;
    }

    public function setDpoAddressStreet1(?string $dpoAddressStreet1): void
    {
        $this->dpoAddressStreet1 = $dpoAddressStreet1;
    }

    public function getDpoAddressStreet2(): ?string
    {
        return $this->dpoAddressStreet2;
    }

    public function setDpoAddressStreet2(?string $dpoAddressStreet2): void
    {
        $this->dpoAddressStreet2 = $dpoAddressStreet2;
    }

    public function getDpoAddressPostalCode(): ?string
    {
        return $this->dpoAddressPostalCode;
    }

    public function setDpoAddressPostalCode(?string $dpoAddressPostalCode): void
    {
        $this->dpoAddressPostalCode = $dpoAddressPostalCode;
    }

    public function getDpoAddressCity(): ?string
    {
        return $this->dpoAddressCity;
    }

    public function setDpoAddressCity(?string $dpoAddressCity): void
    {
        $this->dpoAddressCity = $dpoAddressCity;
    }

    public function getDpoAddressState(): ?string
    {
        return $this->dpoAddressState;
    }

    public function setDpoAddressState(?string $dpoAddressState): void
    {
        $this->dpoAddressState = $dpoAddressState;
    }

    public function getDpoAddressCountry(): ?string
    {
        return $this->dpoAddressCountry;
    }

    public function setDpoAddressCountry(?string $dpoAddressCountry): void
    {
        $this->dpoAddressCountry = $dpoAddressCountry;
    }

    public function getTosEnabled(): bool
    {
        return $this->tosEnabled;
    }

    public function setTosEnabled(bool $tosEnabled): void
    {
        $this->tosEnabled = $tosEnabled;
    }

    public function getTosTemplate(): ?Template
    {
        return $this->tosTemplate;
    }

    public function setTosTemplate(?Template $tosTemplate = null): void
    {
        $this->tosTemplate = $tosTemplate;
    }

    public function getPrivacyTemplate(): ?Template
    {
        return $this->privacyTemplate;
    }

    public function setPrivacyTemplate(?Template $privacyTemplate = null): void
    {
        $this->privacyTemplate = $privacyTemplate;
    }
}
