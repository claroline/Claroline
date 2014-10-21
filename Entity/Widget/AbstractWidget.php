<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\PortfolioBundle\Entity\Portfolio;

/**
 * @ORM\Table(name="icap__portfolio_abstract_widget")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="widget_type", type="string")
 * @ORM\DiscriminatorMap({
 *      "title"           = "TitleWidget",
 *      "userInformation" = "UserInformationWidget",
 *      "skills"          = "SkillsWidget",
 *      "text"            = "TextWidget",
 *      "formations"      = "FormationsWidget",
 *      "badges"          = "BadgesWidget"
 * })
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
     * @var integer
     *
     * @ORM\Column(name="col", type="integer", options={"default" = 1})
     */
    protected $column = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="row", type="integer", options={"default" = 1})
     */
    protected $row = 1;

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

    protected $widgetType = null;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $column
     *
     * @return AbstractWidget
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param int $row
     *
     * @return AbstractWidget
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
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

    /**
     * @param \Icap\PortfolioBundle\Entity\Portfolio $portfolio
     *
     * @return AbstractWidget
     */
    public function setPortfolio(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return array();
    }

    /**
     * @return array
     */
    abstract public function getData();

    /**
     * @return array
     */
    abstract public function getEmpty();
}
