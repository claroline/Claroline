<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Annotation\ORM as ORMExt;

/**
 * Invalid because the "@Extendable" annotation has no discriminatorColumn attribute.
 * 
 * @ORM\Entity
 * @ORM\Table(name="claro_test_invalid_disc_column_1")
 * @ORMExt\Extendable()
 */
class InvalidDiscriminatorColumn1
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