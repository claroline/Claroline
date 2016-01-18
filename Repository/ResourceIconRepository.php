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

class ResourceIconRepository extends EntityRepository
{

    public function findCustomIcons()
    {
        $dql = 'SELECT i FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon i
            WHERE i.relativeUrl LIKE :search
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%thumbnails%");

        return $query->getResult();
    }

    public function findBaseIcons()
    {
        $dql = 'SELECT i FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon i
            WHERE i.mimeType != :search
            AND i.isShortcut = false
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "custom");

        return $query->getResult();
    }
}
