<?php

namespace Claroline\PrivacyBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_privacy_parameters')]
#[ORM\Entity]
class PrivacyParameters
{
    use Id;

    #[ORM\Column(name: 'country_storage', type: 'string', nullable: true)]
    private ?string $countryStorage = null;

    #[ORM\Column(name: 'dpo_name', type: 'string', nullable: true)]
    private ?string $dpoName = null;

    #[ORM\Column(name: 'dpo_email', type: 'string', nullable: true)]
    private ?string $dpoEmail = null;

    #[ORM\Column(name: 'dpo_phone', type: 'string', nullable: true)]
    private ?string $dpoPhone = null;

    #[ORM\Column(name: 'dpo_address_street1', type: 'string', nullable: true)]
    protected ?string $dpoAddressStreet1 = null;

    #[ORM\Column(name: 'dpo_address_street2', type: 'string', nullable: true)]
    protected ?string $dpoAddressStreet2 = null;

    #[ORM\Column(name: 'dpo_address_postal_code', type: 'string', nullable: true)]
    protected ?string $dpoAddressPostalCode = null;

    #[ORM\Column(name: 'dpo_address_city', type: 'string', nullable: true)]
    protected ?string $dpoAddressCity = null;

    #[ORM\Column(name: 'dpo_address_state', type: 'string', nullable: true)]
    protected ?string $dpoAddressState = null;

    #[ORM\Column(name: 'dpo_address_country', type: 'string', nullable: true)]
    protected ?string $dpoAddressCountry = null;

    #[ORM\Column(name: 'tos_enabled', type: 'boolean')]
    private bool $tosEnabled = false;

    
    #[ORM\JoinColumn(name: 'template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Template\Template::class)]
    private ?Template $tosTemplate = null;

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

    public function setTosTemplate(Template $tosTemplate = null): void
    {
        $this->tosTemplate = $tosTemplate;
    }
}
