<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_workspace_shortcuts")
 * @ORM\Entity()
 */
class Shortcuts
{
    use Id;
    use Uuid;

    const SHORTCUTS_LIMIT = 8;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace", inversedBy="shortcuts")
     * @ORM\JoinColumn(name="workspace_id", onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role", inversedBy="shortcuts")
     * @ORM\JoinColumn(name="role_id", onDelete="CASCADE")
     *
     * @var Role
     */
    private $role;

    /**
     * @ORM\Column(name="shortcuts_data", type="json", nullable=true)
     *
     * @var array
     */
    private $data = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        if ($this->workspace) {
            $this->workspace->removeShortcuts($this);
        }

        $this->workspace = $workspace;
        $workspace->addShortcuts($this);
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    public function setRole(Role $role)
    {
        if ($this->role) {
            $this->role->removeShortcuts($this);
        }

        $this->role = $role;
        $role->addShortcuts($this);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
