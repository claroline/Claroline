<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class HomeTabConfigRepository extends EntityRepository
{
    /**
     * Used by list listWorkspaceVisibleHomeTabsForPickerAction in fine (claro_list_visible_workspace_home_tabs_picker).
     */
    public function findVisibleWorkspaceHomeTabConfigsByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT htc, ht
            FROM Claroline\CoreBundle\Entity\Tab\HomeTabConfig htc
            JOIN htc.homeTab ht
            WHERE htc.workspace = :workspace
            AND htc.user IS NULL
            AND htc.type = 'workspace'
            AND htc.visible = true
            ORDER BY htc.tabOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }
}
