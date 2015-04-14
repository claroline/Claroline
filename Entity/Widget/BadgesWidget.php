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
     * @var BadgesWidgetBadge[]|\Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\BadgesWidgetBadge", mappedBy="widget", cascade={"persist", "remove"})
     */
    protected $badges;

    public function __construct()
    {
        $this->badges = new ArrayCollection();
        $this->sizeX  = 4;
        $this->sizeY  = 4;
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\BadgesWidgetBadge[] $badges
     *
     * @return BadgesWidget
     */
    public function setBadges($badges)
    {
        foreach ($badges as $badge) {
            $badge->setWidget($this);
        }

        $this->badges = $badges;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\BadgesWidgetBadge[]
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'children' => array()
        );

        foreach ($this->getBadges() as $userBadge) {
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
        return $this->getBadges();
    }
}
