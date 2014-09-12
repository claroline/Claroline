<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_badges")
 * @ORM\Entity
 */
class BadgesWidget extends AbstractWidget
{
    protected $widgetType = 'badges';

    /**
     * @var BadgesWidgetUserBadge[]|\Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\BadgesWidgetUserBadge", mappedBy="widget", cascade={"persist", "remove"})
     */
    protected $userBadges;

    public function __construct()
    {
        $this->userBadges = new ArrayCollection();
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\BadgesWidgetUserBadge[] $userbadges
     *
     * @return BadgesWidget
     */
    public function setUserBadges($userbadges)
    {
        foreach ($userbadges as $userBagde) {
            $userBagde->setWidget($this);
        }

        $this->userBadges = $userbadges;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\BadgesWidgetUserBadge[]
     */
    public function getUserBadges()
    {
        return $this->userBadges;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'id'       => $this->getId(),
            'children' => array()
        );

        foreach ($this->getUserBadges() as $userBadge) {
            $data['children'][] = $userBadge->getData();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'children' => array()
        );
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->getUserBadges();
    }
}
