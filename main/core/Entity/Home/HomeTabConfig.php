<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\HomeTabConfigRepository")
 * @ORM\Table(
 *     name="claro_home_tab_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="home_tab_config_unique_home_tab_user_workspace_type",
 *             columns={"home_tab_id", "user_id", "workspace_id", "type"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"homeTab", "user", "workspace", "type"})
 */
class HomeTabConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_home_tab"})
     * @SerializedName("configId")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Home\HomeTab"
     * )
     * @ORM\JoinColumn(name="home_tab_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_home_tab"})
     * @SerializedName("hometab")
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
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column()
     * @Groups({"api_home_tab"})
     * @SerializedName("type")
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     * @Groups({"api_home_tab"})
     * @SerializedName("visible")
     */
    protected $visible = true;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     * @Groups({"api_home_tab"})
     * @SerializedName("locked")
     */
    protected $locked = false;

    /**
     * @ORM\Column(type="integer", name="tab_order")
     * @Groups({"api_home_tab"})
     * @SerializedName("tabOrder")
     */
    protected $tabOrder;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_home_tab"})
     * @SerializedName("details")
     */
    protected $details;

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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }
}
