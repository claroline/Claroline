<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Name
{
    /**
     * @ORM\Column(name="entity_name")
     */
    protected ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
