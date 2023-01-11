<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

class UpdateResourceEvaluations implements AsyncHighMessageInterface
{
    /** @var int */
    private $resourceNodeId;
    /** @var int[] */
    private $userIds;
    /** @var string */
    private $status;
    /** @var bool */
    private $withCreation;

    public function __construct(
        int $resourceNodeId,
        array $userIds,
        ?string $status = AbstractEvaluation::STATUS_NOT_ATTEMPTED,
        ?bool $withCreation = true
    ) {
        $this->resourceNodeId = $resourceNodeId;
        $this->userIds = $userIds;
        $this->status = $status;
        $this->withCreation = $withCreation;
    }

    public function getResourceNodeId(): int
    {
        return $this->resourceNodeId;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getWithCreation(): bool
    {
        return $this->withCreation;
    }
}
