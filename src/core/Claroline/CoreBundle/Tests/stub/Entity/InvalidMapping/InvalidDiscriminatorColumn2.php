<?php

namespace Claroline\CoreBundle\Tests\stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Annotation\ORM as ORMExt;

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