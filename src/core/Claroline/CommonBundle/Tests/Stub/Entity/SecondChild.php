<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

/**
 * @ORM\Entity
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class SecondChild extends Ancestor
{
    /**
     * @ORM\Column(type="string", length=256)
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