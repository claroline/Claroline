<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_ancestor")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class Ancestor
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
    private $ancestorField;
    
    public function getId()
    {
        return $this->id;
    }

    public function getAncestorField()
    {
        return $this->ancestorField;
    }

    public function setAncestorField($value)
    {
        $this->ancestorField = $value;
    }
}