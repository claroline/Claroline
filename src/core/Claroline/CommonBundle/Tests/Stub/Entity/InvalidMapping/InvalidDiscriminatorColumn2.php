<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

/**
 * Invalid because the "discriminatorColumn" attribute of the "@Extendable"
 * annotation has an empty value.
 * 
 * @ORM\Entity
 * @ORM\Table(name="claro_test_invalid_disc_column_2")
 * @ORMExt\Extendable(discriminatorColumn="")
 */
class InvalidDiscriminatorColumn2
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