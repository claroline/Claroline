<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_seconddescendant")
 */
class SecondDescendant extends FirstChild
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $secondDescendantField;
        
    public function getSecondDescendantField()
    {
        return $this->secondDescendantField;
    }
    
    public function setSecondDescendantField($value)
    {
        $this->secondDescendantField = $value;
    }
}