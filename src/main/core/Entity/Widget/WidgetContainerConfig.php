<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetContainer entity.
 */
#[ORM\Table(name: 'claro_widget_container_config')]
#[ORM\Entity]
class WidgetContainerConfig
{
    use Id;
    use Uuid;

    /**
     * The name of the widget.
     *
     *
     * @var string
     */
    #[ORM\Column(name: 'widget_name', nullable: true)]
    private $name;

    /**
     * Widget name align (left, center, right).
     *
     *
     * @var string
     */
    #[ORM\Column]
    private $alignName = 'left';

    #[ORM\Column(type: 'boolean', name: 'is_visible')]
    protected $visible = true;

    /**
     * The display layout of the container.
     *
     * NB:
     *   Each element in the array represents a column. The value, represents the ratio of the column.
     *
     * Example: [2, 1]
     *   The layout has 2 columns, the first one is 2/3 width and the second is 1/3.
     *
     * @var array
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $layout = [];

    /**
     * The color of the text inside the widget.
     *
     *
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $color = null;

    /**
     * The color of the border of the widget.
     *
     *
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $borderColor = null;

    /**
     * The type of the background (none, color, image).
     *
     *
     * @var string
     */
    #[ORM\Column]
    private $backgroundType = 'none';

    /**
     * The background data (either the color or the image url).
     *
     *
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $background = null;

    /**
     * The position of the instance inside its container.
     *
     *
     * @var int
     */
    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private $position = 0;

    /**
     *
     * @var WidgetContainer
     */
    #[ORM\JoinColumn(name: 'widget_container_id', onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Widget\WidgetContainer::class, inversedBy: 'widgetContainerConfigs', cascade: ['persist'])]
    protected $widgetContainer;

    /**
     * WidgetContainer constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name align.
     *
     * @return string
     */
    public function getAlignName()
    {
        return $this->alignName;
    }

    /**
     * Set name align.
     *
     * @param string $alignName
     */
    public function setAlignName($alignName)
    {
        $this->alignName = $alignName;
    }

    /**
     * Get layout.
     *
     * @return array
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get border color.
     *
     * @return string
     */
    public function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * Set border color.
     *
     * @param string $borderColor
     */
    public function setBorderColor($borderColor)
    {
        $this->borderColor = $borderColor;
    }

    /**
     * Get background type.
     *
     * @return string
     */
    public function getBackgroundType()
    {
        return $this->backgroundType;
    }

    /**
     * Set background type.
     *
     * @param string $backgroundType
     */
    public function setBackgroundType($backgroundType)
    {
        $this->backgroundType = $backgroundType;
    }

    /**
     * Get background.
     *
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Set background.
     *
     * @param string $background
     */
    public function setBackground($background)
    {
        $this->background = $background;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function setWidgetContainer(WidgetContainer $container)
    {
        $this->widgetContainer = $container;
        $container->addWidgetContainerConfig($this);
    }

    public function getWidgetContainer()
    {
        return $this->widgetContainer;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }
}
