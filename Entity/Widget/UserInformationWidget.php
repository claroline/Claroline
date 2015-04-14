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
     * @var bool
     *
     * @ORM\Column(type="boolean", name="show_avatar")
     */
    protected $showAvatar = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="show_mail")
     */
    protected $showMail = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="show_phone")
     */
    protected $showPhone = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="show_description")
     */
    protected $showDescription = false;

    public function __construct()
    {
        $this->sizeX = 2;
        $this->sizeY = 2;
    }

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
     * @return boolean
     */
    public function isShowAvatar()
    {
        return $this->showAvatar;
    }

    /**
     * @param boolean $showAvatar
     *
     * @return UserInformationWidget
     */
    public function setShowAvatar($showAvatar)
    {
        $this->showAvatar = $showAvatar;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowMail()
    {
        return $this->showMail;
    }

    /**
     * @param boolean $showMail
     *
     * @return UserInformationWidget
     */
    public function setShowMail($showMail)
    {
        $this->showMail = $showMail;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowPhone()
    {
        return $this->showPhone;
    }

    /**
     * @param boolean $showPhone
     *
     * @return UserInformationWidget
     */
    public function setShowPhone($showPhone)
    {
        $this->showPhone = $showPhone;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowDescription()
    {
        return $this->showDescription;
    }

    /**
     * @param boolean $showDescription
     *
     * @return UserInformationWidget
     */
    public function setShowDescription($showDescription)
    {
        $this->showDescription = $showDescription;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $birthDate = $this->getBirthDate();

        return array(
            'city' => $this->getCity(),
            'birthDate' => $birthDate ? $birthDate->format('Y/m/d') : $birthDate,
            'show_avatar' => $this->isShowAvatar(),
            'show_mail' => $this->isShowMail(),
            'show_phone' => $this->isShowPhone(),
            'show_description' => $this->isShowDescription()

        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'city' => null,
            'birthDate' => null,
            'show_avatar' => false,
            'show_mail' => false,
            'show_phone' => false,
            'show_description' => false
        );
    }
}
