<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\PortfolioBundle\Entity\Portfolio;

/**
 * @ORM\Table(name="icap__portfolio_abstract_widget")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="widget_type", type="string")
 * @ORM\DiscriminatorMap({"title" = "TitleWidget", "userInformation" = "UserInformationWidget"})
 */
abstract class AbstractWidget
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
     * @var \Datetime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \Datetime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var Portfolio
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="widgets")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $portfolio;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Portfolio
     */
    public function getPortfolio()
    {
        return $this->portfolio;
    }
}
