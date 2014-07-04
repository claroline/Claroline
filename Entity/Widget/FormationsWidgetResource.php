<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_formations_resource")
 * @ORM\Entity
 */
class FormationsWidgetResource implements SubWidgetInterface
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
     * @var ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\FormationsWidget", inversedBy="resources")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", nullable=false)
     */
    private $widget;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     *
     * @return FormationsWidgetFormation
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $widget
     *
     * @return SkillsWidgetSkill
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $resource = $this->getResource();

        return array(
            'resource' => $resource->getId(),
            'id'       => $resource->getId(),
            'name'     => $resource->getPathForDisplay()
        );
    }
}
 