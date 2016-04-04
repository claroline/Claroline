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

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class DirectoryRepository extends EntityRepository
{
    public function findRootDirectories()
    {
        $dql = "SELECT directory FROM Claroline\CoreBundle\Entity\Resource\Directory directory
            JOIN directory.resourceNode node
            WHERE node.parent is NULL
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findDefaultUploadDirectories(Workspace $workspace)
    {
        $dql = "SELECT directory FROM Claroline\CoreBundle\Entity\Resource\Directory directory
            JOIN directory.resourceNode node
            JOIN node.workspace workspace
            WHERE workspace.id = {$workspace->getId()}
            AND directory.isUploadDestination = true
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
