<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_badges_badge")
 * @ORM\Entity
 */
class BadgesWidgetBadge implements SubWidgetInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Claroline\CoreBundle\Entity\Badge\Badge
     *
     * @ORM\ManyToOne(targetEntity="\Claroline\CoreBundle\Entity\Badge\Badge")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false)
     */
    private $badge;

    /**
     * @var \Icap\PortfolioBundle\Entity\Widget\BadgesWidget
     *
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\BadgesWidget", inversedBy="badges")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", nullable=false)
     */
    private $widget;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return BadgesWidgetBadge
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param BadgesWidget $badgesWidget
     *
     * @return BadgesWidget
     */
    public function setWidget(BadgesWidget $badgesWidget)
    {
        $this->widget = $badgesWidget;

        return $this;
    }

    /**
     * @return BadgesWidget
     */
    public function getBadgesWidget()
    {
        return $this->widget;
    }

    /**
     * @param Badge $badge
     *
     * @return BadgesWidgetBadge
     */
    public function setBadge(Badge $badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return UserBadge
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'badge' => $this->badge->getId(),
            'name'  => $this->badge->getName(),
            'img'   => $this->badge->getWebPath()
        );
    }
}
 