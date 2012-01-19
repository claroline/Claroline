<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Annotation\ORM as ORMExt;

/**
 * Conflictual because you can't have an "@InheritanceType" along with 
 * an "@Extendable" annotation (it'll always be a class table inheritance
 * under the hood).
 * 
 * @ORM\Entity
 * @ORM\Table(name="claro_test_conflictual_mapping_1")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 * @ORM\InheritanceType("JOINED")
 */
class ConflictualMapping1
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}