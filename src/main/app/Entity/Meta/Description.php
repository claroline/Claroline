<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Description
{
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     */
    protected $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }
}
