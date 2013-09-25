<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

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
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
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
     *
     * @var Widget
     */
    protected $widget;

    /**
     * @ORM\Column(name="is_admin", type="boolean")
     */
    protected $isAdmin = false;

    /**
     * @ORM\Column(name="is_desktop", type="boolean")
     */
    protected $isDesktop = false;

    /**
     * @ORM\Column(name="name")
     */
    protected $name = 'Change me !';

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
}
