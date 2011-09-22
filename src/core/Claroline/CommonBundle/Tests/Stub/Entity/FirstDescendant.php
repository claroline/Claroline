<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FirstDescendant extends FirstChild
{
    /**
     * @ORM\Column(type="string", length=256)
     */
    private $firstDescendantField;
        
    public function getFirstDescendantField()
    {
        return $this->firstDescendantField;
    }

    public function setFirstDescendantField($firstDescendantField)
    {
        $this->firstDescendantField = $firstDescendantField;
    }
}