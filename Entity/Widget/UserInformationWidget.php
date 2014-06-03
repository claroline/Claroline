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
     * @return array
     */
    public function getData()
    {
        return array(
            'city' => $this->getCity()
        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'city' => null
        );
    }
}
