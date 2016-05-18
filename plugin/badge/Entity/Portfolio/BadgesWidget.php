<?php

namespace Icap\BadgeBundle\Entity\Portfolio;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;

/**
 * @ORM\Table(name="icap__portfolio_widget_badges")
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\PortfolioWidgetRepository")
 */
class BadgesWidget extends AbstractWidget
{
    const WIDGET_TYPE = 'badges';
    const SIZE_X = 4;
    const SIZE_Y = 4;

    protected $widgetType = self::WIDGET_TYPE;

    /**
     * @var BadgesWidgetBadge[]|\Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Icap\BadgeBundle\Entity\Portfolio\BadgesWidgetBadge", mappedBy="widget", cascade={"persist", "remove"})
     */
    protected $badges;

    public function __construct()
    {
        $this->badges = new ArrayCollection();
    }

    /**
     * @param \Icap\BadgeBundle\Entity\Portfolio\BadgesWidgetBadge[] $badges
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
     * @return \Icap\BadgeBundle\Entity\Portfolio\BadgesWidgetBadge[]
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
            'children' => array(),
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
            'children' => array(),
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
