<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @ORM\Table(name="icap__blog_widget_list_options")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\WidgetListOptionsRepository")
 */
class WidgetListOptions
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false, unique=true)
     *
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     */
    protected $widgetInstance;

    /**
     * @var bool
     *
     * @ORM\Column(type="string", length=1)
     */
    protected $displayStyle = 'b';

    public function getId()
    {
        return $this->id;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setDisplayStyle($displayStyle)
    {
        $this->displayStyle = $displayStyle;

        return $this;
    }

    public function getDisplayStyle()
    {
        return $this->displayStyle;
    }
}
