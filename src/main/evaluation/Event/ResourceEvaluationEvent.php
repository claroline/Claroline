<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
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
    /** @var ResourceEvaluation */
    private $attempt;

    public function __construct(ResourceUserEvaluation $evaluation, ?ResourceEvaluation $attempt = null)
    {
        $this->evaluation = $evaluation;
        $this->attempt = $attempt;
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

    /**
     * Get the current attempt.
     */
    public function getAttempt(): ?ResourceEvaluation
    {
        return $this->attempt;
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->evaluation->getResourceNode();
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans(
            'resourceEvaluation',
            [
                'userName' => $this->getUser()->getUsername(),
                'resourceName' => $this->getResourceNode()->getName(),
                'statusName' => $this->evaluation->getStatus(),
                'userProgression' => $this->evaluation->getProgression().'/'.$this->evaluation->getProgressionMax(),
                'durationTime' => $this->evaluation->getDuration(),
            ],
            'resource'
        );
    }
}
