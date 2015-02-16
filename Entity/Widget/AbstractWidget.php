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
 *      "badges"          = "BadgesWidget",
 *      "experience"      = "ExperienceWidget"
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
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $label;

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
     * @var integer
     *
     * @ORM\Column(name="size_x", type="integer", options={"default" = 1})
     */
    protected $sizeX = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="size_y", type="integer", options={"default" = 1})
     */
    protected $sizeY = 1;

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
     * @var string
     */
    protected $widgetType = null;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param int $column
     *
     * @return $this
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
     * @return $this
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
     * @return int
     */
    public function getSizeX()
    {
        return $this->sizeX;
    }

    /**
     * @param int $sizeX
     *
     * @return AbstractWidget
     */
    public function setSizeX($sizeX)
    {
        $this->sizeX = $sizeX;

        return $this;
    }

    /**
     * @return int
     */
    public function getSizeY()
    {
        return $this->sizeY;
    }

    /**
     * @param int $sizeY
     *
     * @return AbstractWidget
     */
    public function setSizeY($sizeY)
    {
        $this->sizeY = $sizeY;

        return $this;
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
     * @return $this
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
     * @param string $widgetType
     *
     * @return $this
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
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
    public function getCommonData()
    {
        return array(
            'label'  => $this->getLabel(),
            'type'   => $this->getWidgetType(),
            'row'    => $this->getRow(),
            'col'    => $this->getColumn(),
            'sizeX'  => $this->getSizeX(),
            'sizeY'  => $this->getSizeY()
        );
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
