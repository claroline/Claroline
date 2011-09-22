<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SecondDescendant extends FirstChild
{
    /**
     * @ORM\Column(type="string", length=256)
     */
    private $secondDescendantField;
        
    public function getSecondDescendantField()
    {
        return $this->secondDescendantField;
    }

    public function setFirstDescendantField($secondDescendantField)
    {
        $this->secondDescendantField = $secondDescendantField;
    }
}