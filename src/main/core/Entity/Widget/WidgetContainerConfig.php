<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(name: 'widget_name', nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $alignName = 'left';

    #[ORM\Column(name: 'is_visible', type: Types::BOOLEAN)]
    protected bool $visible = true;

    /**
     * The display layout of the container.
     *
     * NB:
     *   Each element in the array represents a column. The value, represents the ratio of the column.
     *
     * Example: [2, 1]
     *   The layout has 2 columns, the first one is 2/3 width and the second is 1/3.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $layout = [];

    /**
     * The color of the text inside the widget.
     */
    #[ORM\Column(nullable: true)]
    private ?string $color = null;

    /**
     * The color of the border of the widget.
     */
    #[ORM\Column(nullable: true)]
    private ?string $borderColor = null;

    /**
     * The type of the background (none, color, image).
     */
    #[ORM\Column]
    private ?string $backgroundType = 'none';

    /**
     * The background data (either the color or the image url).
     */
    #[ORM\Column(nullable: true)]
    private ?string $background = null;

    /**
     * The position of the instance inside its container.
     */
    #[ORM\Column(name: 'position', type: Types::INTEGER, nullable: true)]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: WidgetContainer::class, cascade: ['persist'], inversedBy: 'widgetContainerConfigs')]
    #[ORM\JoinColumn(name: 'widget_container_id', nullable: true, onDelete: 'CASCADE')]
    private ?WidgetContainer $widgetContainer = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAlignName(): ?string
    {
        return $this->alignName;
    }

    public function setAlignName(?string $alignName): void
    {
        $this->alignName = $alignName;
    }

    public function getLayout(): ?array
    {
        return $this->layout;
    }

    public function setLayout(array $layout): void
    {
        $this->layout = $layout;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor): void
    {
        $this->borderColor = $borderColor;
    }

    public function getBackgroundType(): ?string
    {
        return $this->backgroundType;
    }

    public function setBackgroundType(?string $backgroundType): void
    {
        $this->backgroundType = $backgroundType;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): void
    {
        $this->background = $background;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function setWidgetContainer(WidgetContainer $container): void
    {
        $this->widgetContainer = $container;
        $container->addWidgetContainerConfig($this);
    }

    public function getWidgetContainer(): ?WidgetContainer
    {
        return $this->widgetContainer;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }
}
