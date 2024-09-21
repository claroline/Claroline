<?php

namespace Claroline\AppBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractUserPreferences
{
    use Id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    protected ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
