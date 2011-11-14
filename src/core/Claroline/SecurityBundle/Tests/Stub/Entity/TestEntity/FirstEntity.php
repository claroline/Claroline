<?php

namespace Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Library\Annotation as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_security_first_entity")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class FirstEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstEntityField;
    
    public function getId()
    {
        return $this->id;
    }

    public function getFirstEntityField()
    {
        return $this->firstEntityField;
    }

    public function setFirstEntityField($value)
    {
        $this->firstEntityField = $value;
    }
}