<?php

namespace Icap\PortfolioBundle\Entity;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_groups",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_groups_unique_idx", columns={"portfolio_id", "group_id"})
 *      }
 * )
 * @ORM\Entity
 */
class PortfolioGroup
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
     * @var \Claroline\CoreBundle\Entity\Group
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioGroups")
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
     * @param \Claroline\CoreBundle\Entity\Group $group
     *
     * @return PortfolioUser
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
