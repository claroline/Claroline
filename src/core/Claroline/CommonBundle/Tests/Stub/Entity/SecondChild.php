<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="stub_common_second_child")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class SecondChild extends Ancestor
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $secondChildField;

    public function getSecondChildField()
    {
        return $this->secondChildField;
    }

    public function setSecondChildField($value)
    {
        $this->secondChildField = $value;
    }
}