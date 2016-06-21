<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\TeamBundle\Entity\Team;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_teams",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_teams_unique_idx", columns={"portfolio_id", "team_id"})
 *      }
 * )
 * @ORM\Entity
 */
class PortfolioTeam
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Claroline\TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="Claroline\TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=false)
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioTeams")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", nullable=false)
     */
    protected $portfolio;

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
     * @param \Claroline\TeamBundle\Entity\Team $team
     *
     * @return PortfolioUser
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return \Claroline\TeamBundle\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}
