<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_activity")
 */
class Activity extends AbstractResource
{
    /**
     * @ORM\Column(type="string")
     */
    protected $instruction;

    /**
     * Returns the instruction.
     *
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Sets the instruction.
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }
}