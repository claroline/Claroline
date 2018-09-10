<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tab;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab")
 */
class HomeTab
{
    use Id;
    use Poster;
    use Uuid;

    const TYPE_WORKSPACE = 'workspace';
    const TYPE_DESKTOP = 'desktop';
    const TYPE_ADMIN_DESKTOP = 'administration';

    /**
     * @ORM\Column(nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    private $workspace = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="HomeTabConfig",
     *     mappedBy="homeTab"
     * )
     */
    private $homeTabConfigs;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetContainer",
     *     mappedBy="homeTab"
     * )
     */
    private $widgetContainers;

    /**
     * HomeTab constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->homeTabConfigs = new ArrayCollection();
        $this->widgetContainers = new ArrayCollection();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWidgetContainers()
    {
        return $this->widgetContainers;
    }

    public function addWidgetContainer(WidgetContainer $widgetContainer)
    {
        if (!$this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->add($widgetContainer);
        }
    }

    public function removeWidgetContainer(WidgetContainer $widgetContainer)
    {
        if ($this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->removeElement($widgetContainer);
        }
    }

    public function getHomeTabConfigs()
    {
        return $this->homeTabConfigs;
    }
}
