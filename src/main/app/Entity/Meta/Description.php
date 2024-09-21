<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A short description of an entity.
 * It SHOULD be available in the minimal representation of the entity to be displayed in lists/cards.
 */
trait Description
{
    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description = null): void
    {
        $this->description = $description;
    }
}
