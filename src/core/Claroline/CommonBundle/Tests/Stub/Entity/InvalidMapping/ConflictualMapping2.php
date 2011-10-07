<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

// Disabled -> @ORM\Entity

/**
 * Conflictual because you can't have a "@DiscriminatorColumn" along with 
 * an "@Extendable" annotation (which already has the discriminator column's 
 * name as an attribute).
 * 
 * 
 * @ORM\Table(name="claro_test_conflictual_mapping_2")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class ConflictualMapping2
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