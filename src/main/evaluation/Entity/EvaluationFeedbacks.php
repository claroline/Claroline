<?php

namespace Claroline\EvaluationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EvaluationFeedbacks
{
    /**
     * @ORM\Column(name="success_message", type="text", nullable=true)
     */
    private ?string $successMessage = null;

    /**
     * @ORM\Column(name="failure_message", type="text", nullable=true)
     */
    private ?string $failureMessage = null;

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(string $successMessage = null): void
    {
        $this->successMessage = $successMessage;
    }

    public function getFailureMessage(): ?string
    {
        return $this->failureMessage;
    }

    public function setFailureMessage(string $failureMessage = null): void
    {
        $this->failureMessage = $failureMessage;
    }
}
