<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\UserEvaluationEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Event dispatched when a resource evaluation is created or updated.
 */
class EvaluateResourceEvent extends UserEvaluationEvent
{
    /** @var ResourceEvaluation */
    private $attempt;
    /** @var ResourceUserEvaluation */
    private $evaluation;

    public function __construct(ResourceUserEvaluation $evaluation, ResourceEvaluation $attempt)
    {
        parent::__construct($evaluation);

        $this->evaluation = $evaluation;
        $this->attempt = $attempt;
    }

    /**
     * Get the current attempt.
     */
    public function getAttempt(): ResourceEvaluation
    {
        return $this->attempt;
    }

    public function getUser(): User
    {
        return $this->evaluation->getUser();
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
