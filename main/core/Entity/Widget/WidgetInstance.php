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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WidgetInstanceRepository")
 * @ORM\Table(name="claro_widget_instance")
 */
class WidgetInstance
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_widget"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\Widget")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * @Groups({"api_widget"})
     * @SerializedName("widget")
     *
     * @var Widget
     */
    protected $widget;

    /**
     * @ORM\Column(name="is_admin", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isAdmin")
     */
    protected $isAdmin = false;

    /**
     * @ORM\Column(name="is_desktop", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isDesktop")
     */
    protected $isDesktop = false;

    /**
     * @ORM\Column(name="name")
     * @Groups({"api_widget"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_widget"})
     * @SerializedName("icon")
     */
    protected $icon;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig",
     *     mappedBy="widgetInstance"
     * )
     */
    protected $widgetDisplayConfigs;

    /**
     * @ORM\Column(name="template", nullable=true)
     * @Groups({"api_widget"})
     * @SerializedName("template")
     */
    protected $template;

    public function getId()
    {
        return $this->id;
    }

    public function setWidget($widget)
    {
        $this->widget = $widget;
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function setIsAdmin($bool)
    {
        $this->isAdmin = $bool;
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isDesktop()
    {
        return $this->isDesktop;
    }

    public function setIsDesktop($bool)
    {
        $this->isDesktop = $bool;
    }

    public function serializeForWidgetPicker()
    {
        $return = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        return $return;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getWidgetDisplayConfigs()
    {
        return $this->widgetDisplayConfigs;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
