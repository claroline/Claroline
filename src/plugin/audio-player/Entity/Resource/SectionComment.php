<?php

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_audio_resource_section_comment')]
#[ORM\Entity]
class SectionComment
{
    use Id;
    use Uuid;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\JoinColumn(name: 'section_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Section::class, inversedBy: 'comments')]
    private ?Section $section = null;

    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $editionDate = null;

    public function __construct()
    {
        $this->refreshUuid();
        $this->creationDate = new \DateTime();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate(): ?\DateTimeInterface
    {
        return $this->editionDate;
    }

    public function setEditionDate(\DateTimeInterface $editionDate = null): void
    {
        $this->editionDate = $editionDate;
    }
}
