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
        $query->setParameter('search', '%thumbnails%');

        return $query->getResult();
    }

    public function findBaseIcons()
    {
        $dql = 'SELECT i FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon i
            WHERE i.mimeType != :search
            AND i.isShortcut = false
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', 'custom');

        return $query->getResult();
    }

    public function findByMimeTypes($mimeTypes, $includeShortcuts = false)
    {
        // If mimetypes is empty, return no elements
        if (empty($mimeTypes)) {
            return [];
        }
        // If not array turn to array
        if (!is_array($mimeTypes)) {
            $mimeTypes = [$mimeTypes];
        }
        $qb = $this->createQueryBuilder('i')
            ->select('i');
        $qb
            ->andWhere($qb->expr()->in('i.mimeType', '?1'))
            ->setParameter(1, $mimeTypes);
        if (!$includeShortcuts) {
            $qb
                ->andWhere('i.isShortcut = ?2')
                ->setParameter(2, false);
        }

        return $qb->getQuery()->getResult();
    }
}
