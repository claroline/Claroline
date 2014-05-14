<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\PortfolioBundle\Entity\Portfolio;

/**
 * @ORM\Table(name="icap__portfolio_widget_node")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\Widget\WidgetNodeRepository")
 */
class WidgetNode
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
     * @var \Icap\PortfolioBundle\Entity\Widget\WidgetType
     *
     * @ORM\ManyToOne(
     *     targetEntity="Icap\PortfolioBundle\Entity\Widget\WidgetType",
     *     inversedBy="widgetNodes",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="widget_type_id", onDelete="CASCADE", nullable=false)
     */
    protected $widgetType;

    /**
     * @var Portfolio
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioUsers")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", nullable=false)
     */
    protected $portfolio;

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
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return WidgetNode
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
}
 