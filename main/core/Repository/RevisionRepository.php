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
use Claroline\CoreBundle\Entity\Resource\Text;

class RevisionRepository extends EntityRepository
{
    /**
     * Returns the last revision of a text.
     *
     * @param Text $text
     *
     * @return Revision
     */
    public function getLastRevision(Text $text)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\Revision r
            JOIN r.text t2
            WHERE r.version = (SELECT MAX(r2.version) FROM Claroline\CoreBundle\Entity\Resource\Revision r2
            JOIN r2.text t WHERE t.id = {$text->getId()})
            and t2.id = {$text->getId()}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
}
