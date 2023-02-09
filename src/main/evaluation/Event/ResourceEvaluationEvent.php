<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Event dispatched when a resource evaluation is created or updated.
 */
class ResourceEvaluationEvent extends Event
{
    /** @var ResourceUserEvaluation */
    private $evaluation;
    /** @var array */
    private $changes;

    public function __construct(ResourceUserEvaluation $evaluation, array $changes)
    {
        $this->evaluation = $evaluation;
        $this->changes = $changes;
    }

    /**
     * Get the current evaluation.
     */
    public function getEvaluation(): ResourceUserEvaluation
    {
        return $this->evaluation;
    }

    public function getUser(): User
    {
        return $this->evaluation->getUser();
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->evaluation->getResourceNode();
    }

    public function hasStatusChanged(): bool
    {
        return $this->changes['status'];
    }

    public function hasProgressionChanged(): bool
    {
        return $this->changes['progression'];
    }

    public function hasScoreChanged(): bool
    {
        return $this->changes['score'];
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans(
            'resourceEvaluation',
            [
                'userName' => $this->getUser()->getUsername(),
                'resourceName' => $this->getResourceNode()->getName(),
                'statusName' => $this->evaluation->getStatus(),
                'userProgression' => $this->evaluation->getProgression().' %',
                'durationTime' => $this->evaluation->getDuration(),
            ],
            'resource'
        );
    }
}
