<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_guides",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_users_unique_idx", columns={"portfolio_id", "user_id"})
 *      }
 * )
 * @ORM\Entity
 */
class PortfolioGuide
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioGuides")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", nullable=false)
     */
    protected $portfolio;

    /**
     * @ORM\Column(name="comments_view_at", type="datetime")
     */
    protected $commentsViewAt;

    public function __construct()
    {
        $this->commentsViewAt = new \DateTime();
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return PortfolioUser
     */
    public function setPortfolio(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return Portfolio
     */
    public function getPortfolio()
    {
        return $this->portfolio;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return PortfolioUser
     */
    public function setUser(User $user)
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
     * @return mixed
     */
    public function getCommentsViewAt()
    {
        return $this->commentsViewAt;
    }

    /**
     * @param mixed $commentsViewAt
     *
     * @return PortfolioGuide
     */
    public function setCommentsViewAt($commentsViewAt)
    {
        $this->commentsViewAt = $commentsViewAt;

        return $this;
    }
}