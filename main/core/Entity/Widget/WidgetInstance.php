<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WidgetInstanceRepository")
 * @ORM\Table(name="claro_widget_instance")
 */
class WidgetInstance
{
    use Id;
    use Uuid;

    /**
     * The name of the instance.
     *
     * @ORM\Column(name="widget_name")
     *
     * @var string
     */
    private $name;

    /**
     * The widget which is rendered.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\Widget")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var Widget
     */
    private $widget;

    /**
     * The color of the text inside the widget.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $color = null;

    /**
     * The type of the background (none, color, image).
     *
     * @ORM\Column()
     *
     * @var string
     */
    private $backgroundType = 'none';

    /**
     * The background data (either the color or the image url).
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $background = null;

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
     * Get widget.
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Set widget.
     *
     * @param Widget $widget
     */
    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;
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
}
