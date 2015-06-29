<?php

namespace Icap\PortfolioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;

/**
 * @ORM\Table(name="icap__portfolio_widget")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\PortfolioWidgetRepository")
 */
class PortfolioWidget
{
    /**
     * @var Portfolio
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Portfolio", inversedBy="portfolioWidgets")
     * @ORM\JoinColumn(name="portfolio_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $portfolio;

    /**
     * @var AbstractWidget
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\AbstractWidget", inversedBy="portfolioWidgets")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $widget;

    /**
     * @var integer
     *
     * @ORM\Column(name="col", type="integer", options={"default" = 0})
     */
    protected $column;

    /**
     * @var integer
     *
     * @ORM\Column(name="row", type="integer", options={"default" = 0})
     */
    protected $row;

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
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $widgetType;

    /**
     * @return Portfolio
     */
    public function getPortfolio()
    {
        return $this->portfolio;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return PortfolioWidget
     */
    public function setPortfolio($portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return AbstractWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param AbstractWidget $widget
     *
     * @return PortfolioWidget
     */
    public function setWidget(AbstractWidget $widget)
    {
        $this->widget = $widget;

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
     * @param int $column
     *
     * @return PortfolioWidget
     */
    public function setColumn($column)
    {
        $this->column = $column;

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
     * @param int $row
     *
     * @return PortfolioWidget
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
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
     * @return PortfolioWidget
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
     * @return PortfolioWidget
     */
    public function setSizeY($sizeY)
    {
        $this->sizeY = $sizeY;

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
     * @return PortfolioWidget
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }

    /**
     * @param array $position
     *
     * @return PortfolioWidget
     */
    public function setSize(array $position)
    {
        $this->setSizeX($position['sizeX']);
        return $this->setSizeY($position['sizeY']);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'portfolio_id' => $this->getPortfolio()->getId(),
            'widget_id' => $this->getWidget()->getId(),
            'widget_type' => $this->getWidgetType(),
            'row' => $this->getRow(),
            'column' => $this->getColumn(),
            'sizeX' => $this->getSizeX(),
            'sizeY' => $this->getSizeY()
        ];
    }
}
