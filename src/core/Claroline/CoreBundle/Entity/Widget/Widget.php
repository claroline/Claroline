<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_widget")
 */
class Widget
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    protected $plugin;


    /**
     * @ORM\Column(type="string")
     */
    protected $name;


    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetAdminOption")
     * @ORM\JoinColumn(name="admin_workspace_option_id", referencedColumnName="id")
     */
    protected $workspaceOption;

    public function getId()
    {
        return $this->id;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWorkspaceOption()
    {
        return $this->workspaceOption;
    }

    public function setWorkspaceOption($option)
    {
        $this->workspaceOption = $option;
    }
}
