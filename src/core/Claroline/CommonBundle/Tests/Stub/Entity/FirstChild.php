<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="stub_common_first_child")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class FirstChild extends Ancestor
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstChildField;

    public function getFirstChildField()
    {
        return $this->firstChildField;
    }

    public function setFirstChildField($value)
    {
        $this->firstChildField = $value;
    }
}