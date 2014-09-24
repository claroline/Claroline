<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_badges_user_badge")
 * @ORM\Entity
 */
class BadgesWidgetUserBadge implements SubWidgetInterface
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
     * @var \Claroline\CoreBundle\Entity\Badge\UserBadge
     *
     * @ORM\ManyToOne(targetEntity="\Claroline\CoreBundle\Entity\Badge\UserBadge", inversedBy="badges")
     * @ORM\JoinColumn(name="user_badge_id", referencedColumnName="id", nullable=false)
     */
    private $userBadge;

    /**
     * @var \Icap\PortfolioBundle\Entity\Widget\BadgesWidget
     *
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\BadgesWidget", inversedBy="skills")
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
     * @param UserBadge $userBadge
     *
     * @return BadgesWidgetUserBadge
     */
    public function setUserBadge(UserBadge $userBadge)
    {
        $this->userBadge = $userBadge;

        return $this;
    }

    /**
     * @return UserBadge
     */
    public function getUserBadge()
    {
        return $this->userBadge;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $badge = $this->userBadge->getBadge();

        return array(
            'id'   => $this->userBadge->getId(),
            'name' => $badge->getName(),
            'img'  => $badge->getWebPath()
        );
    }
}
 