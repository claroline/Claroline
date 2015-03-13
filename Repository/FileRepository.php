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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class FileRepository extends EntityRepository
{
    public function findDirectoryChildren(ResourceNode $parent)
    {
        $dql = "
            SELECT file FROM Claroline\CoreBundle\Entity\Resource\File file
            JOIN file.resourceNode node
            JOIN node.parent parent
            WHERE parent.id = {$parent->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
