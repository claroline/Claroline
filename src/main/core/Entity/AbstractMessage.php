<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass]
abstract class AbstractMessage
{
    use Id;
    use Uuid;

    #[ORM\Column(name: 'content', type: Types::TEXT)]
    protected ?string $content = null;

    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTimeInterface $updated = null;

    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $creator = null;

    #[ORM\Column(nullable: true)]
    protected ?string $author = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->creationDate = new \DateTime();
        $this->updated = new \DateTime();
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setCreator(User $creator): void
    {
        $this->creator = $creator;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $date): void
    {
        $this->creationDate = $date;
    }

    public function setModificationDate(\DateTimeInterface $date): void
    {
        $this->updated = $date;
    }

    public function getModificationDate(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }
}
