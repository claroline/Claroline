<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ArchivedAt
{
    use Archived;

    #[ORM\Column(name: 'archivedAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $archivedAt = null;

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeInterface $archivedAt = null): void
    {
        $this->archivedAt = $archivedAt;
    }
}
