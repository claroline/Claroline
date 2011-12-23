<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Annotation\ORM as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_node_second_child")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class SecondChild extends TreeAncestor
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