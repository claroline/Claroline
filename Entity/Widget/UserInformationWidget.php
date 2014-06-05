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
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

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
     * @param string $description
     *
     * @return UserInformationWidget
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'city'        => $this->getCity(),
            'description' => $this->getDescription()
        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'city'        => null,
            'description' => null
        );
    }
}
