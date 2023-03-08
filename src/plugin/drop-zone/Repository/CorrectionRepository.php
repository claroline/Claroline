<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Repository;

use Claroline\DropZoneBundle\Entity\Dropzone;
use Doctrine\ORM\EntityRepository;

class CorrectionRepository extends EntityRepository
{
    public function findAllCorrectionsByDropzone(Dropzone $dropzone)
    {
        $dql = '
            SELECT c
            FROM Claroline\DropZoneBundle\Entity\Correction c
            JOIN c.drop drop
            JOIN drop.dropzone d
            WHERE d = :dropzone
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);

        return $query->getResult();
    }
}
