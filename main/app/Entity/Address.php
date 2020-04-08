<?php

namespace Claroline\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Address
{
    /**
     * @ORM\Column(name="address_street1", nullable=true)
     *
     * @var string
     */
    private $addressStreet1;

    /**
     * @ORM\Column(name="address_street2", nullable=true)
     *
     * @var string
     */
    private $addressStreet2;

    /**
     * @ORM\Column(name="address_postal_code", nullable=true)
     *
     * @var string
     */
    private $addressPostalCode;

    /**
     * @ORM\Column(name="address_city", nullable=true)
     *
     * @var string
     */
    private $addressCity;

    /**
     * @ORM\Column(name="address_state", nullable=true)
     *
     * @var string
     */
    private $addressState;

    /**
     * @ORM\Column(name="address_country", nullable=true)
     *
     * @var string
     */
    private $addressCountry;

    /**
     * @return string
     */
    public function getAddress()
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

    /**
     * @return string
     */
    public function getAddressStreet1()
    {
        return $this->addressStreet1;
    }

    /**
     * @param string $addressStreet1
     */
    public function setAddressStreet1($addressStreet1)
    {
        $this->addressStreet1 = $addressStreet1;
    }

    /**
     * @return string
     */
    public function getAddressStreet2()
    {
        return $this->addressStreet2;
    }

    /**
     * @param string $addressStreet2
     */
    public function setAddressStreet2($addressStreet2)
    {
        $this->addressStreet2 = $addressStreet2;
    }

    /**
     * @return string
     */
    public function getAddressPostalCode()
    {
        return $this->addressPostalCode;
    }

    /**
     * @param string $addressPostalCode
     */
    public function setAddressPostalCode($addressPostalCode)
    {
        $this->addressPostalCode = $addressPostalCode;
    }

    /**
     * @return string
     */
    public function getAddressCity()
    {
        return $this->addressCity;
    }

    /**
     * @param string $addressCity
     */
    public function setAddressCity($addressCity)
    {
        $this->addressCity = $addressCity;
    }

    /**
     * @return string
     */
    public function getAddressState()
    {
        return $this->addressState;
    }

    /**
     * @param string $addressState
     */
    public function setAddressState($addressState)
    {
        $this->addressState = $addressState;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @param string $addressCountry
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }
}
