<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProgression
 * Represents the progression of a User in a Step.
 *
 *
 */
#[ORM\Table(name: 'innova_path_progression')]
#[ORM\Entity]
class UserProgression
{
    use Id;

    /**
     * Step for which we track the progression.
     *
     *
     */
    #[ORM\JoinColumn(name: 'step_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Innova\PathBundle\Entity\Step::class)]
    private Step $step;

    /**
     * User for which we track the progression.
     *
     *
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    private ?User $user = null;

    /**
     * Current state of the Step.
     */
    #[ORM\Column(name: 'progression_status', type: 'string')]
    private string $status = 'seen';

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(Step $step): void
    {
        $this->step = $step;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
