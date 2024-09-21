<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\FavouriteBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_workspace_favourite')]
#[ORM\UniqueConstraint(columns: ['workspace_id', 'user_id'])]
#[ORM\Entity]
class WorkspaceFavourite extends AbstractFavourite
{
    /**
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private $workspace;

    /**
     * Get workspace.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set workspace.
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
