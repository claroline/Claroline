<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_formations_formation")
 * @ORM\Entity
 */
class FormationsWidgetFormation implements SubWidgetInterface
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
    protected $name;

    /**
     * @var ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\FormationsWidget", inversedBy="formations")
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
     * @param string $name
     *
     * @return SkillsWidgetSkill
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
        return array(
            'name'     => $this->getName(),
            'resource' => array(
                'id'   => $this->getResource()->getId(),
                'name' => $this->getResource()->getName()
            )
        );
    }
}
 