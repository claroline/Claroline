<?php

namespace Claroline\AppBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractUserView
{
    use Id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?User $user = null;

    #[ORM\Column(name: 'seen_at', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected ?\DateTimeInterface $seenAt = null;

    #[ORM\Column(name: 'view_count', type: Types::INTEGER, nullable: false)]
    protected ?int $count = 0;

    /**
     * Get the viewed object.
     */
    abstract public function getSubject(): object;

    /**
     * Set the viewed object.
     */
    abstract public function setSubject(object $subject): void;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function incrementCount(): void
    {
        ++$this->count;
    }

    public function getSeenAt(): ?\DateTimeInterface
    {
        return $this->seenAt;
    }

    public function setSeenAt(\DateTimeInterface $seenAt): void
    {
        $this->seenAt = $seenAt;
    }
}
