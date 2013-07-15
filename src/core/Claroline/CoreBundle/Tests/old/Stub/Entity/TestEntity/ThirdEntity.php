<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_security_third_entity")
 */
class ThirdEntity
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
    private $thirdEntityField;

    public function getId()
    {
        return $this->id;
    }

    public function getThirdEntityField()
    {
        return $this->thirdEntityField;
    }

    public function setThirdEntityField($value)
    {
        $this->thirdEntityField = $value;
    }
}