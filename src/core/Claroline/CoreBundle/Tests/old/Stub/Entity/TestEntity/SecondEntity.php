<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_security_second_entity")
 */
class SecondEntity
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
    private $secondEntityField;

    public function getId()
    {
        return $this->id;
    }

    public function getSecondEntityField()
    {
        return $this->secondEntityField;
    }

    public function setSecondEntityField($value)
    {
        $this->secondEntityField = $value;
    }
}