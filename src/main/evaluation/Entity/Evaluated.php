<?php

namespace Claroline\EvaluationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Evaluated
{
    /**
     * The evaluation will produce a score.
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected bool $evaluated = false;

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected bool $required = false;

    /**
     * The estimated time required to do the resource (in minutes).
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $estimatedDuration = null;

    /**
     * Is the entity evaluated ?
     */
    public function isEvaluated(): bool
    {
        return $this->evaluated;
    }

    /**
     * Sets the evaluated flag.
     */
    public function setEvaluated(bool $evaluated): void
    {
        $this->evaluated = $evaluated;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(int $estimatedDuration = null): void
    {
        $this->estimatedDuration = $estimatedDuration;
    }
}
