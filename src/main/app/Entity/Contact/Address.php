<?php

namespace Claroline\AppBundle\Entity\Contact;

use Doctrine\ORM\Mapping as ORM;

trait Address
{
    #[ORM\Column(name: 'address_street1', nullable: true)]
    protected ?string $addressStreet1 = null;

    #[ORM\Column(name: 'address_street2', nullable: true)]
    protected ?string $addressStreet2 = null;

    #[ORM\Column(name: 'address_postal_code', nullable: true)]
    protected ?string $addressPostalCode = null;

    #[ORM\Column(name: 'address_city', nullable: true)]
    protected ?string $addressCity = null;

    #[ORM\Column(name: 'address_state', nullable: true)]
    protected ?string $addressState = null;

    #[ORM\Column(name: 'address_country', nullable: true)]
    protected ?string $addressCountry = null;

    public function getAddress(): string
    {
        return trim(join(PHP_EOL, [
            $this->addressStreet1 ?? '',
            $this->addressStreet2 ?? '',
            $this->addressCity ?? '',
            $this->addressState ?? '',
            $this->addressPostalCode ?? '',
            $this->addressCountry ?? '',
        ]));
    }

    public function getAddressStreet1(): ?string
    {
        return $this->addressStreet1;
    }

    public function setAddressStreet1(?string $addressStreet1): void
    {
        $this->addressStreet1 = $addressStreet1;
    }

    public function getAddressStreet2(): ?string
    {
        return $this->addressStreet2;
    }

    public function setAddressStreet2(?string $addressStreet2): void
    {
        $this->addressStreet2 = $addressStreet2;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): void
    {
        $this->addressPostalCode = $addressPostalCode;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): void
    {
        $this->addressCity = $addressCity;
    }

    public function getAddressState(): ?string
    {
        return $this->addressState;
    }

    public function setAddressState(?string $addressState): void
    {
        $this->addressState = $addressState;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): void
    {
        $this->addressCountry = $addressCountry;
    }
}
