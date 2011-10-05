<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

// Disabled -> @ORM\Entity

/**
 * Conflictual because you can't have an "@InheritanceType" along with 
 * an "@Extendable" annotation (it'll always be a class table inheritance
 * under the hood).
 * 
 * 
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