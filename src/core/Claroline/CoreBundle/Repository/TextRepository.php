<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\Text;

class TextRepository extends EntityRepository
{
    public function getLastRevision(Text $text)
    {
        $dql = "SELECT r FROM Claroline\CoreBundle\Entity\Resource\Revision r
            WHERE r.id IN (SELECT max(r_1.id) FROM Claroline\CoreBundle\Entity\Resource\Revision r_1
            JOIN r_1.text t
            WHERE t.id = {$text->getId()})";

        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
}
