<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_widget_user_information")
 * @ORM\Entity
 */
class UserInformationWidget extends AbstractWidget
{
    protected $widgetType = 'userInformation';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $birthDate;

    /**
     * @param string $city
     *
     * @return UserInformationWidget
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \DateTime $birthDate
     *
     * @return UserInformationWidget
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'city'      => $this->getCity(),
            'birthDate' => $this->getBirthDate()
        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'city'      => null,
            'birthDate' => null
        );
    }
}
