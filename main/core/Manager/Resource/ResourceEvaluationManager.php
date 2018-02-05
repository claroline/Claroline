<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\ResourceEvaluationEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.manager.resource_evaluation_manager")
 */
class ResourceEvaluationManager
{
    private $eventDispatcher;
    private $om;

    private $resourceUserEvaluationRepo;
    private $resourceEvaluationRepo;
    private $logRepo;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectManager            $om
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ObjectManager $om)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;

        $this->resourceUserEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceUserEvaluation');
        $this->resourceEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceEvaluation');
        $this->logRepo = $this->om->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user, $withCreation = true)
    {
        $evaluation = $this->resourceUserEvaluationRepo->findOneBy(['resourceNode' => $node, 'user' => $user]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = $this->createResourceUserEvaluation($node, $user);
        }

        return $evaluation;
    }

    public function persistResourceUserEvaluation(ResourceUserEvaluation $evaluation)
    {
        $this->om->persist($evaluation);
        $this->om->flush();
    }

    public function createResourceUserEvaluation(ResourceNode $node, User $user = null)
    {
        $evaluation = new ResourceUserEvaluation();
        $evaluation->setResourceNode($node);
        $evaluation->setUser($user);
        $this->persistResourceUserEvaluation($evaluation);

        return $evaluation;
    }

    public function persistResourceEvaluation(ResourceEvaluation $evaluation)
    {
        $this->om->persist($evaluation);
        $this->om->flush();
    }

    public function createResourceEvaluation(
        ResourceNode $node,
        User $user = null,
        \DateTime $date = null,
        $status = null,
        $score = null,
        $scoreMin = null,
        $scoreMax = null,
        $customScore = null,
        $duration = null,
        $comment = null,
        $data = null,
        $forceStatus = false
    ) {
        $this->om->startFlushSuite();
        $resourceUserEvaluation = $this->getResourceUserEvaluation($node, $user);
        $evaluation = new ResourceEvaluation();
        $evaluation->setResourceUserEvaluation($resourceUserEvaluation);
        $evaluationDate = $date ? $date : new \DateTime();
        $evaluation->setDate($evaluationDate);
        $evaluation->setStatus($status);
        $evaluation->setScore($score);
        $evaluation->setScoreMin($scoreMin);
        $evaluation->setScoreMax($scoreMax);
        $evaluation->setCustomScore($customScore);
        $evaluation->setDuration($duration);
        $evaluation->setComment($comment);
        $evaluation->setData($data);
        $this->persistResourceEvaluation($evaluation);
        $this->updateResourceUserEvaluation($evaluation, $forceStatus);
        $this->eventDispatcher->dispatch('resource_evaluation', new ResourceEvaluationEvent($resourceUserEvaluation));
        $this->om->endFlushSuite();

        return $evaluation;
    }

    public function updateResourceUserEvaluationData(
        ResourceNode $node,
        User $user = null,
        \DateTime $date = null,
        $status = null,
        $score = null,
        $scoreMin = null,
        $scoreMax = null,
        $customScore = null,
        $duration = null,
        $forceStatus = false,
        $incAttempts = true,
        $incOpenings = false
    ) {
        $rue = $this->getResourceUserEvaluation($node, $user);
        $statusPriority = AbstractResourceEvaluation::STATUS_PRIORITY;

        if (!empty($date)) {
            $rue->setDate($date);
        }
        if (!empty($duration)) {
            $rueDuration = $rue->getDuration() ? $rue->getDuration() : 0;
            $rueDuration += $duration;
            $rue->setDuration($rueDuration);
        }
        if ($forceStatus) {
            $rue->setScore($score);
            $rue->setScoreMax($scoreMax);
            $rue->setScoreMin($scoreMin);
            $rue->setCustomScore($customScore);
        } elseif (!empty($score)) {
            $newScore = empty($scoreMax) ? $score : $score / $scoreMax;

            $rueScore = $rue->getScore() ? $rue->getScore() : 0;
            $rueScoreMax = $rue->getScoreMax();
            $oldScore = empty($rueScoreMax) ? $rueScore : $rueScore / $rueScoreMax;

            if ($newScore >= $oldScore) {
                $rue->setScore($score);
                $rue->setScoreMax($scoreMax);
                $rue->setScoreMin($scoreMin);
            }
        }
        if ($forceStatus ||
            empty($rue->getStatus()) ||
            $statusPriority[$status] > $statusPriority[$rue->getStatus()]
        ) {
            $rue->setStatus($status);
        }
        if ($incAttempts) {
            $nbAttempts = $rue->getNbAttempts() ? $rue->getNbAttempts() : 0;
            ++$nbAttempts;
            $rue->setNbAttempts($nbAttempts);
        }
        if ($incOpenings) {
            $nbOpenings = $rue->getNbOpenings() ? $rue->getNbOpenings() : 0;
            ++$nbOpenings;
            $rue->setNbOpenings($nbOpenings);
        }
        $this->persistResourceUserEvaluation($rue);
    }

    private function updateResourceUserEvaluation(
        ResourceEvaluation $evaluation,
        $forceStatus = false,
        $incAttempts = true
    ) {
        $rue = $evaluation->getResourceUserEvaluation();
        $rue->setDate($evaluation->getDate());

        $duration = $evaluation->getDuration();
        $score = $evaluation->getScore();
        $scoreMax = $evaluation->getScoreMax();
        $scoreMin = $evaluation->getScoreMin();

        $statusPriority = AbstractResourceEvaluation::STATUS_PRIORITY;

        if (!empty($duration)) {
            $rueDuration = $rue->getDuration() ? $rue->getDuration() : 0;
            $rueDuration += $duration;
            $rue->setDuration($rueDuration);
        }
        if ($forceStatus) {
            $rue->setScore($score);
            $rue->setScoreMax($scoreMax);
            $rue->setScoreMin($scoreMin);
            $rue->setCustomScore($evaluation->getCustomScore());
        } elseif (!empty($score)) {
            $newScore = empty($scoreMax) ? $score : $score / $scoreMax;

            $rueScore = $rue->getScore() ? $rue->getScore() : 0;
            $rueScoreMax = $rue->getScoreMax();
            $oldScore = empty($rueScoreMax) ? $rueScore : $rueScore / $rueScoreMax;

            if ($newScore >= $oldScore) {
                $rue->setScore($score);
                $rue->setScoreMax($scoreMax);
                $rue->setScoreMin($evaluation->getScoreMin());
            }
        }
        if ($forceStatus ||
            empty($rue->getStatus()) ||
            ($evaluation->isSuccessful() && !$rue->isSuccessful()) ||
            $statusPriority[$evaluation->getStatus()] > $statusPriority[$rue->getStatus()]
        ) {
            $rue->setStatus($evaluation->getStatus());
        }
        if ($incAttempts) {
            $nbAttempts = $rue->getNbAttempts() ? $rue->getNbAttempts() : 0;
            ++$nbAttempts;
            $rue->setNbAttempts($nbAttempts);
        }
        $this->persistResourceUserEvaluation($rue);
    }

    public function getLogsForResourceTracking(ResourceNode $node, User $user, array $actions, \DateTime $startDate = null)
    {
        return $this->logRepo->findLogsForResourceTracking($node, $user, $actions, $startDate);
    }
}
