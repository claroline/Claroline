<?php

namespace Claroline\EvaluationBundle\Entity\Parameters;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractEvaluationParameters
{
    use Id;

    /**
     * A score will be produced at the end of the evaluation.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $scored = false;

    /**
     * If true, the users who pass the evaluation are anonymized in results.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $anonymized = false;

    #[ORM\Column(name: 'score_total', type: Types::FLOAT, nullable: true)]
    private ?float $scoreTotal = 100;

    /**
     * The conditions to get a success status for the evaluation
     * (e.g., minimal score, min successful resources, max failed resources).
     */
    #[ORM\Column(name: 'success_condition', type: Types::JSON, nullable: true)]
    private ?array $successCondition = [];

    /**
     * A message to display at the end of the evaluation.
     */
    #[ORM\Column(name: 'end_message', type: Types::TEXT, nullable: true)]
    private ?string $endMessage = null;

    #[ORM\Column(name: 'success_message', type: Types::TEXT, nullable: true)]
    private ?string $successMessage = null;

    #[ORM\Column(name: 'failure_message', type: Types::TEXT, nullable: true)]
    private ?string $failureMessage = null;

    public function isScored(): bool
    {
        return $this->scored;
    }

    public function setScored(bool $scored): void
    {
        $this->scored = $scored;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized;
    }

    public function setAnonymized(bool $anonymized): void
    {
        $this->anonymized = $anonymized;
    }

    public function getScoreTotal(): ?float
    {
        return $this->scoreTotal;
    }

    public function setScoreTotal(?float $scoreTotal = null): void
    {
        $this->scoreTotal = $scoreTotal;
    }

    public function getSuccessCondition(): ?array
    {
        return $this->successCondition;
    }

    public function setSuccessCondition(?array $successCondition = []): void
    {
        $this->successCondition = $successCondition;
    }

    public function getEndMessage(): ?string
    {
        return $this->endMessage;
    }

    public function setEndMessage(?string $endMessage = null): void
    {
        $this->endMessage = $endMessage;
    }

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(?string $successMessage = null): void
    {
        $this->successMessage = $successMessage;
    }

    public function getFailureMessage(): ?string
    {
        return $this->failureMessage;
    }

    public function setFailureMessage(?string $failureMessage = null): void
    {
        $this->failureMessage = $failureMessage;
    }
}
