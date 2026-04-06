<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An annotation is a piece of feedback written by a trainer on a learner's submission
 * for a specific milestone. Used in iterative mode to provide ongoing guidance
 * before the final correction.
 */
#[ORM\Table(name: 'claro_project_annotation')]
#[ORM\Entity]
class Annotation
{
    use Id;
    use Uuid;

    /**
     * The annotation text content (rich text HTML).
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    /**
     * Date when the annotation was created.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $date;

    /**
     * The submission this annotation is attached to.
     */
    #[ORM\JoinColumn(name: 'submission_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Submission::class, inversedBy: 'annotations')]
    private ?Submission $submission = null;

    /**
     * The milestone this annotation relates to.
     */
    #[ORM\JoinColumn(name: 'milestone_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Milestone::class)]
    private ?Milestone $milestone = null;

    /**
     * The trainer who wrote this annotation.
     */
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct()
    {
        $this->refreshUuid();
        $this->date = new \DateTime();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    public function setSubmission(?Submission $submission): void
    {
        $this->submission = $submission;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): void
    {
        $this->milestone = $milestone;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
