<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_security_first_entity_child")
 */
class FirstEntityChild extends FirstEntity
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
    private $firstEntityChildField;

    public function getId()
    {
        return $this->id;
    }

    public function getFirstEntityChildField()
    {
        return $this->firstEntityChildField;
    }

    public function setFirstEntityChildField($value)
    {
        $this->firstEntityChildField = $value;
    }
}