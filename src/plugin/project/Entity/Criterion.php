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

#[ORM\Table(name: 'claro_project_criterion')]
#[ORM\Entity]
class Criterion
{
    use Id;
    use Uuid;

    /**
     * The project resource this criterion belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'criteria')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /**
     * Name of the criterion as defined by the trainer (e.g. "Propreté", "Pertinence").
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label = '';

    /**
     * Maximum score for this criterion (e.g. 2 for "Propreté /2", 4 for "Pertinence /4").
     * The total of all criteria scoreMax is computed automatically to produce the overall grade.
     */
    #[ORM\Column(name: 'score_max', type: Types::FLOAT)]
    private float $scoreMax = 0;

    /**
     * Display order of the criterion within the evaluation rubric.
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
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
}
