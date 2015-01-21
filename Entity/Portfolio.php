<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\WidgetNode;

/**
 * @ORM\Table(name="icap__portfolio")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\PortfolioRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Portfolio
{
    const VISIBILITY_NOBODY              = 0;
    const VISIBILITY_NOBODY_LABEL        = 'visibile_to_me';
    const VISIBILITY_USER                = 1;
    const VISIBILITY_USER_LABEL          = 'visible_for_some_users';
    const VISIBILITY_PLATFORM_USER       = 2;
    const VISIBILITY_PLATFORM_USER_LABEL = 'visible_for_platform_user';
    const VISIBILITY_EVERYBODY           = 3;
    const VISIBILITY_EVERYBODY_LABEL     = 'visible_for_everybody';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="integer", name="visibility", nullable=false)
     */
    protected $visibility = self::VISIBILITY_NOBODY;

    /**
     * @var integer
     *
     * @ORM\Column(name="disposition", type="integer")
     */
    protected $disposition = 0;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="PortfolioUser", mappedBy="portfolio", cascade={"all"})
     */
    protected $portfolioUsers;

    /**
     * @ORM\OneToMany(targetEntity="PortfolioGuide", mappedBy="portfolio", cascade={"all"})
     */
    protected $portfolioGuides;

    /**
     * @ORM\OneToMany(targetEntity="PortfolioGroup", mappedBy="portfolio", cascade={"all"})
     */
    protected $portfolioGroups;

    /**
     * @ORM\OneToMany(targetEntity="PortfolioTeam", mappedBy="portfolio", cascade={"all"})
     */
    protected $portfolioTeams;

    /**
     * @var \Icap\PortfolioBundle\Entity\Widget\WidgetNode[]
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\AbstractWidget", mappedBy="portfolio")
     */
    protected $widgets;

    /**
     * @var \Icap\PortfolioBundle\Entity\PortfolioComment[]
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\PortfolioComment", mappedBy="portfolio", cascade={"persist"})
     */
    protected $comments;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @ORM\Column(name="comments_view_at", type="datetime")
     */
    protected $commentsViewAt;

    public function __construct()
    {
        $this->commentsViewAt = new \DateTime();
        $this->widgets        = new ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return Portfolio
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $visibility
     *
     * @return Portfolio
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return array
     */
    public static function getVisibilityLabels()
    {
        return array(
            self::VISIBILITY_NOBODY        => self::VISIBILITY_NOBODY_LABEL,
            self::VISIBILITY_USER          => self::VISIBILITY_USER_LABEL,
            self::VISIBILITY_PLATFORM_USER => self::VISIBILITY_PLATFORM_USER_LABEL,
            self::VISIBILITY_EVERYBODY     => self::VISIBILITY_EVERYBODY_LABEL
        );
    }

    /**
     * @return mixed
     */
    public function getVisibilityLabel()
    {
        $visibilityLabels = self::getVisibilityLabels();
        return $visibilityLabels[$this->getVisibility()];
    }

    /**
     * @param int $disposition
     *
     * @return Portfolio
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * @param PortfolioUser[] $portfolioUsers
     *
     * @return Portfolio
     */
    public function setPortfolioUsers($portfolioUsers)
    {
        foreach ($portfolioUsers as $portfolioUser) {
            $portfolioUser->setPortfolio($this);
        }

        $this->portfolioUsers = $portfolioUsers;

        return $this;
    }

    /**
     * @return PortfolioUser[]|ArrayCollection
     */
    public function getPortfolioUsers()
    {
        return $this->portfolioUsers;
    }

    /**
     * @return PortfolioGuide[]
     */
    public function getPortfolioGuides()
    {
        return $this->portfolioGuides;
    }

    /**
     * @param PortfolioGuide[] $portfolioGuides
     *
     * @return Portfolio
     */
    public function setPortfolioGuides($portfolioGuides)
    {
        foreach ($portfolioGuides as $portfolioGuide) {
            $portfolioGuide->setPortfolio($this);
        }

        $this->portfolioGuides = $portfolioGuides;

        return $this;
    }

    /**
     * @param mixed $portfolioGroups
     *
     * @return Portfolio
     */
    public function setPortfolioGroups($portfolioGroups)
    {
        foreach ($portfolioGroups as $portfolioGroup) {
            $portfolioGroup->setPortfolio($this);
        }

        $this->portfolioGroups = $portfolioGroups;

        return $this;
    }

    /**
     * @return PortfolioGroup[]|ArrayCollection
     */
    public function getPortfolioGroups()
    {
        return $this->portfolioGroups;
    }

    /**
     * @param mixed $portfolioTeams
     *
     * @return Portfolio
     */
    public function setPortfolioTeams($portfolioTeams)
    {
        foreach ($portfolioTeams as $portfolioTeam) {
            $portfolioTeam->setPortfolio($this);
        }

        $this->portfolioTeams = $portfolioTeams;

        return $this;
    }

    /**
     * @return PortfolioTeam[]|ArrayCollection
     */
    public function getPortfolioTeams()
    {
        return $this->portfolioTeams;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Portfolio
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[] $abstractWidgets
     *
     * @return Portfolio
     */
    public function setWidgets($abstractWidgets)
    {
        $this->widgets = $abstractWidgets;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @return Widget\TitleWidget|null
     */
    public function getTitleWidget()
    {
        $titleWidget = null;

        foreach ($this->getWidgets() as $widget) {
            if ('title' === $widget->getWidgetType()) {
                $titleWidget = $widget;
                break;
            }
        }

        return $titleWidget;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasGuide(User $user)
    {
        $isGuide = false;

        foreach ($this->getPortfolioGuides() as $portfolioGuide) {
            if ($user->getId() === $portfolioGuide->getUser()->getId()) {
                $isGuide = true;
                break;
            }
        }

        return $isGuide;
    }

    /**
     * @return PortfolioComment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param PortfolioComment[] $comments
     *
     * @return Portfolio
     */
    public function setComments($comments)
    {
        foreach ($comments as $comment) {
            $comment->setPortfolio($this);
        }

        $this->comments = $comments;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCommentsViewAt()
    {
        return $this->commentsViewAt;
    }

    /**
     * @param \DateTime $commentsViewAt
     *
     * @return Portfolio
     */
    public function setCommentsViewAt($commentsViewAt)
    {
        $this->commentsViewAt = $commentsViewAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountUnreadComments(\DateTime $commentsViewAt = null)
    {
        $countUnreadComments = 0;

        if (null === $commentsViewAt) {
            $commentsViewAt = $this->getCommentsViewAt();
        }

        foreach ($this->getComments() as $comment) {
            if($commentsViewAt < $comment->getSendingDate()) {
                $countUnreadComments++;
            }
        }

        return $countUnreadComments;
    }

    /**
     * @return \Datetime
     */
    public function getLastUpdateDate()
    {
        $lastUpdateDate = null;

        foreach ($this->getWidgets() as $widget) {
            if ($lastUpdateDate < $widget->getUpdatedAt()) {
                $lastUpdateDate = $widget->getUpdatedAt();
            }
        }

        return $lastUpdateDate;
    }
}