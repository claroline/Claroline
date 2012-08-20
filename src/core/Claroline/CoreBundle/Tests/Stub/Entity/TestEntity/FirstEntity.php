<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_security_first_entity")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"first_entity" = "FirstEntity", "first_entity_child" = "FirstEntityChild"})
 */
class FirstEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
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