<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

// Disabled -> @ORM\Entity

/**
 * Conflictual because you can't have a "@DiscriminatorMap" along with 
 * an "@Extendable" annotation (the discriminator map will always be 
 * built dynamically).
 * 
 * 
 * @ORM\Table(name="claro_test_conflictual_mapping_3")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 * @ORM\DiscriminatorMap({"child_1" = "Foo", "child_2" = "Bar"})
 */
class ConflictualMapping3
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