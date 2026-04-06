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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A submission represents a learner's work deposit for a project (or a specific milestone).
 * Depending on the project configuration, it can contain a file, rich text, or a URL.
 */
#[ORM\Table(name: 'claro_project_submission')]
#[ORM\Entity]
class Submission
{
    use Id;
    use Uuid;

    /**
     * The type of content submitted.
     *   - file      : A file was uploaded.
     *   - rich_text : Rich text content written in the platform.
     *   - url       : An external URL.
     */
    #[ORM\Column(name: 'content_type', type: Types::STRING, nullable: true)]
    private ?string $contentType = null;

    /**
     * Rich text content (HTML) when contentType is "rich_text".
     */
    #[ORM\Column(name: 'text_content', type: Types::TEXT, nullable: true)]
    private ?string $textContent = null;

    /**
     * Metadata of the uploaded file when contentType is "file".
     * Stored as JSON: {url, name, mimeType, size}.
     */
    #[ORM\Column(name: 'file_data', type: Types::JSON, nullable: true)]
    private ?array $fileData = null;

    /**
     * External URL when contentType is "url".
     */
    #[ORM\Column(name: 'url_content', type: Types::STRING, nullable: true)]
    private ?string $urlContent = null;

    /**
     * Date and time when the submission was made.
     */
    #[ORM\Column(name: 'submitted_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $submittedDate;

    /**
     * Date and time of the learner's first access to the project.
     * Used to compute relative deadlines (first access + N days).
     */
    #[ORM\Column(name: 'first_access_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $firstAccessDate = null;

    /**
     * The project this submission belongs to.
     */
    #[ORM\JoinColumn(name: 'project_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    private ?Project $project = null;

    /**
     * The milestone this submission is associated with (null for non-iterative projects).
     */
    #[ORM\JoinColumn(name: 'milestone_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Milestone::class)]
    private ?Milestone $milestone = null;

    /**
     * The learner who made this submission.
     */
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    /**
     * Trainer annotations on this submission (used in iterative mode).
     *
     * @var Collection<int, Annotation>
     */
    #[ORM\OneToMany(targetEntity: Annotation::class, mappedBy: 'submission', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $annotations;

    public function __construct()
    {
        $this->refreshUuid();
        $this->submittedDate = new \DateTime();
        $this->annotations = new ArrayCollection();
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getTextContent(): ?string
    {
        return $this->textContent;
    }

    public function setTextContent(?string $textContent): void
    {
        $this->textContent = $textContent;
    }

    public function getFileData(): ?array
    {
        return $this->fileData;
    }

    public function setFileData(?array $fileData): void
    {
        $this->fileData = $fileData;
    }

    public function getUrlContent(): ?string
    {
        return $this->urlContent;
    }

    public function setUrlContent(?string $urlContent): void
    {
        $this->urlContent = $urlContent;
    }

    public function getSubmittedDate(): \DateTimeInterface
    {
        return $this->submittedDate;
    }

    public function setSubmittedDate(\DateTimeInterface $submittedDate): void
    {
        $this->submittedDate = $submittedDate;
    }

    public function getFirstAccessDate(): ?\DateTimeInterface
    {
        return $this->firstAccessDate;
    }

    public function setFirstAccessDate(?\DateTimeInterface $firstAccessDate): void
    {
        $this->firstAccessDate = $firstAccessDate;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
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

    /**
     * @return Annotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations->toArray();
    }

    public function addAnnotation(Annotation $annotation): void
    {
        if (!$this->annotations->contains($annotation)) {
            $this->annotations->add($annotation);
            $annotation->setSubmission($this);
        }
    }

    public function removeAnnotation(Annotation $annotation): void
    {
        if ($this->annotations->contains($annotation)) {
            $this->annotations->removeElement($annotation);
        }
    }
}
