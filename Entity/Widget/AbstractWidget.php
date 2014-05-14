<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass */
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
     * @var \Icap\PortfolioBundle\Entity\Widget\WidgetNode
     *
     * @ORM\OneToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\WidgetNode")
     * @ORM\JoinColumn(name="widget_node_id", onDelete="CASCADE")
     */
    protected $widgetNode;

    /**
     * @param WidgetNode $widgetNode
     */
    public function setWidgetNode(WidgetNode $widgetNode)
    {
        $this->widgetNode = $widgetNode;
    }

    /**
     * @return WidgetNode
     */
    public function getWidgetNode()
    {
        return $this->widgetNode;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
