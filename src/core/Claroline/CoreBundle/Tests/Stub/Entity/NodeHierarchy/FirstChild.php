<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity\NodeHierarchy;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Annotation\ORM as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_node_first_child")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class FirstChild extends TreeAncestor
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