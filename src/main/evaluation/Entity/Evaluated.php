<?php

namespace Claroline\EvaluationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Evaluated
{
    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $evaluated = false;

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $required = false;

    /**
     * The estimated time required to do the resource (in minutes).
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $estimatedDuration;

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
    public function setEvaluated(bool $evaluated)
    {
        $this->evaluated = $evaluated;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required)
    {
        $this->required = $required;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(?int $estimatedDuration = null)
    {
        $this->estimatedDuration = $estimatedDuration;
    }
}
