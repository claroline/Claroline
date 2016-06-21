<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_widget_formations")
 * @ORM\Entity
 */
class FormationsWidget extends AbstractWidget
{
    const WIDGET_TYPE = 'formations';

    const SIZE_X = 4;

    const SIZE_Y = 6;

    protected $widgetType = self::WIDGET_TYPE;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @var FormationsWidgetResource[]|\Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource", mappedBy="widget", cascade={"persist", "remove"})
     */
    protected $resources;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $establishmentName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $diploma;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource[] $resources
     *
     * @return FormationsWidget
     */
    public function setResources($resources)
    {
        foreach ($resources as $resource) {
            $resource->setWidget($this);
        }

        $this->resources = $resources;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param string $name
     *
     * @return FormationsWidget
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return FormationsWidget
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return FormationsWidget
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return string
     */
    public function getEstablishmentName()
    {
        return $this->establishmentName;
    }

    /**
     * @param string $establishmentName
     *
     * @return FormationsWidget
     */
    public function setEstablishmentName($establishmentName)
    {
        $this->establishmentName = $establishmentName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * @param string $diploma
     *
     * @return FormationsWidget
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->getResources();
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'name' => $this->getName(),
            'startDate' => $this->getStartDate() ? $this->getStartDate()->format('Y/m/d') : null,
            'endDate' => $this->getEndDate() ? $this->getEndDate()->format('Y/m/d') : null,
            'establishmentName' => $this->getEstablishmentName(),
            'diploma' => $this->getDiploma(),
            'children' => array(),
        );

        foreach ($this->getResources() as $formation) {
            $data['children'][] = $formation->getData();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'name' => null,
            'startDate' => null,
            'endDate' => null,
            'establishmentName' => null,
            'diploma' => null,
            'children' => array(),
        );
    }
}
