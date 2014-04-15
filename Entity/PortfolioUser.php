<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_users",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_users_unique_idx", columns={"portfolio_id", "user_id"})
 *      }
 * )
 * @ORM\Entity
 */
class PortfolioUser
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
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioUsers")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", nullable=false)
     */
    protected $portfolio;

    /**
     * @param mixed $portfolio
     *
     * @return PortfolioUser
     */
    public function setPortfolio(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return mixed
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
}