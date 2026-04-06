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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A criterion used in rubric-based grading.
 * Each criterion has its own score scale (e.g., "Relevance /4", "Formatting /2").
 * The project's total score is the sum of all criteria scoreMax values.
 */
#[ORM\Table(name: 'claro_project_criteria')]
#[ORM\Entity]
class EvaluationCriteria
{
    use Id;
    use Uuid;

    /**
     * Label of the criterion (e.g., "Relevance", "Formatting", "Originality").
     */
    #[ORM\Column(type: Types::STRING)]
    private string $label = '';

    /**
     * Optional description providing details about how this criterion is evaluated.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Maximum score for this criterion (e.g., 4 for a criterion graded out of 4).
     */
    #[ORM\Column(name: 'score_max', type: Types::FLOAT)]
    private float $scoreMax = 1;

    /**
     * Display order of the criterion within the rubric.
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    /**
     * The project this criterion belongs to.
     */
    #[ORM\JoinColumn(name: 'project_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'criteria')]
    private ?Project $project = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getScoreMax(): float
    {
        return $this->scoreMax;
    }

    public function setScoreMax(float $scoreMax): void
    {
        $this->scoreMax = $scoreMax;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }
}
