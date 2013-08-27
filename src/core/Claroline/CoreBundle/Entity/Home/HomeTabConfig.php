<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_home_tab_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="home_tab_config_unique_home_tab_user",
 *             columns={"home_tab_id", "user_id"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="home_tab_config_unique_home_tab_workspace",
 *             columns={"home_tab_id", "workspace_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"homeTab", "user"})
 * @DoctrineAssert\UniqueEntity({"homeTab", "workspace"})
 */
class HomeTabConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Home\HomeTab"
     * )
     * @ORM\JoinColumn(name="home_tab_id", nullable=false, onDelete="CASCADE")
     */
    protected $homeTab;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="boolean", name="is_visible", nullable=false)
     */
    protected $visible;

    /**
     * @ORM\Column(type="boolean", name="is_locked", nullable=false)
     */
    protected $locked;

    /**
     * @ORM\Column(type="integer", name="tab_order", nullable=false)
     */
    protected $tabOrder;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getHomeTab()
    {
        return $this->homeTab;
    }

    public function setHomeTab($homeTab)
    {
        $this->homeTab = $homeTab;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function getTabOrder()
    {
        return $this->tabOrder;
    }

    public function setTabOrder($tabOrder)
    {
        $this->tabOrder = $tabOrder;
    }
}