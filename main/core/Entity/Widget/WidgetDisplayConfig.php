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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WidgetDisplayConfigRepository")
 * @ORM\Table(
 *     name="claro_widget_display_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="widget_display_config_unique_user",
 *             columns={"widget_instance_id", "user_id"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="widget_display_config_unique_workspace",
 *             columns={"widget_instance_id", "workspace_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"widgetInstance", "workspace"})
 * @DoctrineAssert\UniqueEntity({"widgetInstance", "user"})
 */
class WidgetDisplayConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", onDelete="CASCADE", nullable=true)
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(name="widget_instance_id", onDelete="CASCADE", nullable=false)
     */
    protected $widgetInstance;

    /**
     * @ORM\Column(name="row_position", type="integer")
     */
    protected $row = -1;

    /**
     * @ORM\Column(name="column_position", type="integer")
     */
    protected $column = -1;

    /**
     * @ORM\Column(name="width", type="integer", options={"default":4})
     */
    protected $width = 4;

    /**
     * @ORM\Column(name="height", type="integer", options={"default":3})
     */
    protected $height = 3;

    /**
     * @ORM\Column(name="color", nullable=true)
     */
    protected $color;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function setRow($row)
    {
        $this->row = $row;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setColumn($column)
    {
        $this->column = $column;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }
}
