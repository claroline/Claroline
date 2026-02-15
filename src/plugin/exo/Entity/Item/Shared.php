<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'ujm_share')]
#[ORM\Entity]
class Shared
{
    /**
     * The user with whom the question is shared.
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    /**
     * The shared question.
     */
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Item::class)]
    private ?Item $question = null;

    /**
     * Gives the user the ability to edit and delete the question.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $adminRights = false;

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setQuestion(Item $question): void
    {
        $this->question = $question;
    }

    public function getQuestion(): ?Item
    {
        return $this->question;
    }

    public function setAdminRights(bool $adminRights): void
    {
        $this->adminRights = $adminRights;
    }

    public function hasAdminRights(): bool
    {
        return $this->adminRights;
    }
}
