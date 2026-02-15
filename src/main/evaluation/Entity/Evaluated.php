<?php

namespace Claroline\EvaluationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated
 */
trait Evaluated
{
    /**
     * The evaluation will produce a score.
     *
     * @deprecated
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $evaluated = false;

    /**
     * @deprecated
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $required = false;

    /**
     * The estimated time required to do the resource (in minutes).
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $estimatedDuration = null;

    /**
     * Is the entity evaluated ?
     *
     * @deprecated
     */
    public function isEvaluated(): bool
    {
        return $this->evaluated;
    }

    /**
     * Sets the evaluated flag.
     *
     * @deprecated
     */
    public function setEvaluated(bool $evaluated): void
    {
        $this->evaluated = $evaluated;
    }

    /**
     * @@deprecated
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @@deprecated
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(?int $estimatedDuration = null): void
    {
        $this->estimatedDuration = $estimatedDuration;
    }
}
