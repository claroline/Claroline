<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetContainer entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_container_config")
 *
 * @todo merge with WidgetContainer entity.
 */
class WidgetContainerConfig
{
    use Id;
    use Uuid;
    use Description;

    /**
     * The name of the widget.
     *
     * @ORM\Column(name="widget_name", nullable=true)
     *
     * @var string
     */
    private $name;

    /**
     * Widget name align (left, center, right).
     *
     * @ORM\Column()
     *
     * @var string
     */
    private $alignName = 'left';

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     */
    private $visible = true;

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
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $layout = [];

    /**
     * The color of the text inside the widget (this should be consumed by the widget content in some cases).
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $titleColor = null;

    /**
     * The color of the border of the widget.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $borderColor = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $backgroundColor = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $backgroundUrl = null;

    /**
     * The box shadow (expects a CSS value).
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $boxShadow = null;

    /**
     * The content text color (this should be consumed by the widget content in some cases).
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $textColor = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $maxContentWidth = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $minHeight = null;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $titleLevel = 2;

    /**
     * The position of the instance inside its container.
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     *
     * @var int
     */
    private $position = 0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetContainer",
     *     inversedBy="widgetContainerConfigs",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="widget_container_id", onDelete="CASCADE", nullable=true)
     *
     * @var WidgetContainer
     */
    private $widgetContainer;

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

    public function getTitleColor(): ?string
    {
        return $this->titleColor;
    }

    public function setTitleColor(?string $titleColor)
    {
        $this->titleColor = $titleColor;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor)
    {
        $this->borderColor = $borderColor;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;
    }

    public function getBackgroundUrl(): ?string
    {
        return $this->backgroundUrl;
    }

    public function setBackgroundUrl(?string $backgroundUrl)
    {
        $this->backgroundUrl = $backgroundUrl;
    }

    public function getBoxShadow(): ?string
    {
        return $this->boxShadow;
    }

    public function setBoxShadow(?string $bowShadow): void
    {
        $this->boxShadow = $bowShadow;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(?string $textColor): void
    {
        $this->textColor = $textColor;
    }

    public function getMaxContentWidth(): ?string
    {
        return $this->maxContentWidth;
    }

    public function setMaxContentWidth(?string $maxContentWidth): void
    {
        $this->maxContentWidth = $maxContentWidth;
    }

    public function getMinHeight(): ?string
    {
        return $this->minHeight;
    }

    public function setMinHeight(?string $minHeight): void
    {
        $this->minHeight = $minHeight;
    }

    public function getTitleLevel(): ?int
    {
        return $this->titleLevel;
    }

    public function setTitleLevel(int $titleLevel): void
    {
        $this->titleLevel = $titleLevel;
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
