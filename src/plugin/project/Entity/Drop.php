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
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project_drop')]
#[ORM\Entity]
class Drop
{
    use Id;
    use Uuid;

    /**
     * The project resource this drop belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'drops')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /**
     * The learner who submitted this drop.
     * Null when the project is in group mode (the team is set instead).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * The team that submitted this drop (group mode only).
     * Null when the project is in individual mode (the user is set instead).
     */
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Team $team = null;

    /**
     * Date and time the drop was submitted by the learner or team.
     */
    #[ORM\Column(name: 'drop_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $dropDate;

    /**
     * Whether the learner has finalized the drop.
     * A finalized drop can no longer be modified by the learner.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $finished = false;

    /**
     * The milestone this drop is associated with (iterative mode only).
     * Null for single-submission projects.
     */
    #[ORM\ManyToOne(targetEntity: Milestone::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Milestone $milestone = null;

    /**
     * Documents (files, rich text, URLs, media) attached to this drop.
     *
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'drop', cascade: ['persist', 'remove'])]
    private Collection $documents;

    /**
     * Themed annotations made by the trainer on this drop (iterative mode).
     *
     * @var Collection<int, Annotation>
     */
    #[ORM\OneToMany(targetEntity: Annotation::class, mappedBy: 'drop', cascade: ['persist', 'remove'])]
    private Collection $annotations;

    public function __construct()
    {
        $this->refreshUuid();

        $this->dropDate = new \DateTime();
        $this->documents = new ArrayCollection();
        $this->annotations = new ArrayCollection();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getDropDate(): \DateTimeInterface
    {
        return $this->dropDate;
    }

    public function setDropDate(\DateTimeInterface $dropDate): void
    {
        $this->dropDate = $dropDate;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): void
    {
        $this->milestone = $milestone;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): void
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setDrop($this);
        }
    }

    public function removeDocument(Document $document): void
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
        }
    }

    public function getAnnotations(): Collection
    {
        return $this->annotations;
    }

    public function addAnnotation(Annotation $annotation): void
    {
        if (!$this->annotations->contains($annotation)) {
            $this->annotations->add($annotation);
            $annotation->setDrop($this);
        }
    }

    public function removeAnnotation(Annotation $annotation): void
    {
        if ($this->annotations->contains($annotation)) {
            $this->annotations->removeElement($annotation);
        }
    }
}
