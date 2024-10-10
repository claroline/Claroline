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

#[ORM\MappedSuperclass]
abstract class AbstractComment
{
    use Id;
    use Uuid;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $content = null;

    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $user = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $editionDate = null;

    public function __construct()
    {
        $this->refreshUuid();
        $this->creationDate = new \DateTime();
        $this->editionDate = new \DateTime();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    public function getCreationDate(): ?\DateTimeInterface
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

    public function setEditionDate(?\DateTimeInterface $editionDate = null): void
    {
        $this->editionDate = $editionDate;
    }
}
