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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EvaluateResourceEvent;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourceEvaluationManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $strictDispatcher;

    private $resourceUserEvaluationRepo;
    /** @var LogRepository */
    private $logRepo;
    private $logConnectResource;

    public function __construct(EventDispatcherInterface $eventDispatcher, ObjectManager $om, StrictDispatcher $strictDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->strictDispatcher = $strictDispatcher;

        $this->resourceUserEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceUserEvaluation');
        $this->logRepo = $this->om->getRepository('ClarolineCoreBundle:Log\Log');
        $this->logConnectResource = $this->om->getRepository(LogConnectResource::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user, $withCreation = true)
    {
        $evaluation = $this->resourceUserEvaluationRepo->findOneBy(['resourceNode' => $node, 'user' => $user]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new ResourceUserEvaluation();
            $evaluation->setResourceNode($node);
            $evaluation->setUser($user);

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    /**
     * @return ResourceEvaluation
     */
    public function createResourceEvaluation(ResourceNode $node, User $user = null, \DateTime $date = null, array $data = [])
    {
        $resourceUserEvaluation = $this->getResourceUserEvaluation($node, $user);

        $evaluation = new ResourceEvaluation();
        $evaluation->setResourceUserEvaluation($resourceUserEvaluation);

        $this->updateResourceEvaluation($evaluation, $date, $data);

        return $evaluation;
    }

    public function updateResourceEvaluation(ResourceEvaluation $evaluation, \DateTime $date = null, array $data = [], $incAttempts = true, $incOpenings = false)
    {
        $this->om->startFlushSuite();

        $evaluation->setDate($date ?? new \DateTime());

        if (isset($data['status'])) {
            $evaluation->setStatus($data['status']);
        }
        if (isset($data['score'])) {
            $evaluation->setScore($data['score']);
        }
        if (isset($data['scoreMin'])) {
            $evaluation->setScoreMin($data['scoreMin']);
        }
        if (isset($data['scoreMax'])) {
            $evaluation->setScoreMax($data['scoreMax']);
        }
        if (isset($data['progression'])) {
            $evaluation->setProgression($data['progression']);
        }
        if (isset($data['progressionMax'])) {
            $evaluation->setProgressionMax($data['progressionMax']);
        }
        if (isset($data['duration'])) {
            $evaluation->setDuration($data['duration']);
        }
        if (isset($data['comment'])) {
            $evaluation->setComment($data['comment']);
        }
        if (isset($data['data'])) {
            $evaluation->setData($data['data']);
        }

        $this->om->persist($evaluation);

        $resourceUserEvaluation = $evaluation->getResourceUserEvaluation();

        $resourceUserEvaluation = $this->updateResourceUserEvaluationData($resourceUserEvaluation->getResourceNode(), $resourceUserEvaluation->getUser(), $evaluation->getDate(), [
            'status' => $evaluation->getStatus(),
            'duration' => $evaluation->getDuration(),
            'score' => $evaluation->getScore(),
            'scoreMin' => $evaluation->getScoreMin(),
            'scoreMax' => $evaluation->getScoreMax(),
            'progression' => $evaluation->getProgression(),
            'progressionMax' => $evaluation->getProgressionMax(),
        ], $incAttempts, $incOpenings);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new EvaluateResourceEvent($resourceUserEvaluation, $evaluation), 'resource_evaluation');

        return $evaluation;
    }

    public function updateResourceUserEvaluationData(
        ResourceNode $node,
        User $user = null,
        \DateTime $date = null,
        array $data = [],
        $incAttempts = true,
        $incOpenings = false
    ) {
        $rue = $this->getResourceUserEvaluation($node, $user);

        if (!empty($date)) {
            $rue->setDate($date);
        }

        if (isset($data['status'])
            && (empty($rue->getStatus()) || AbstractEvaluation::STATUS_PRIORITY[$data['status']] > AbstractEvaluation::STATUS_PRIORITY[$rue->getStatus()])
        ) {
            $rue->setStatus($data['status']);
        }

        if (isset($data['duration'])) {
            $rue->setDuration($rue->getDuration() + $data['duration']);
        }

        if (isset($data['score'])) {
            $newScore = empty($data['scoreMax']) ? $data['score'] : $data['score'] / $data['scoreMax'];

            $rueScore = $rue->getScore();
            $rueScoreMax = $rue->getScoreMax();
            $oldScore = empty($rueScoreMax) ? $rueScore : $rueScore / $rueScoreMax;

            if (is_null($oldScore) || $newScore >= $oldScore) {
                $rue->setScore($data['score']);

                if (isset($data['scoreMax'])) {
                    $rue->setScoreMax($data['scoreMax']);
                }
                if (isset($data['scoreMin'])) {
                    $rue->setScoreMin($data['scoreMin']);
                }
            }
        }

        if (isset($data['progression'])) {
            $newProgression = empty($data['progressionMax']) ?
                $data['progression'] :
                $data['progression'] / $data['progressionMax'];

            $rueProgression = $rue->getProgression();
            $rueProgressionMax = $rue->getProgressionMax();
            $oldProgression = empty($rueProgressionMax) ? $rueProgression : $rueProgression / $rueProgressionMax;

            if (is_null($oldProgression) || $newProgression >= $oldProgression) {
                $rue->setProgression($data['progression']);

                if (isset($data['progressionMax'])) {
                    $rue->setProgressionMax($data['progressionMax']);
                }
            }
        }

        if ($incAttempts) {
            $rue->setNbAttempts($rue->getNbAttempts() + 1);
        }
        if ($incOpenings) {
            $rue->setNbOpenings($rue->getNbOpenings() + 1);
        }

        $this->om->persist($rue);
        $this->om->flush();

        return $rue;
    }

    /**
     * @return Log[]
     */
    public function getLogsForResourceTracking(ResourceNode $node, User $user, array $actions, \DateTime $startDate = null)
    {
        return $this->logRepo->findLogsForResourceTracking($node, $user, $actions, $startDate);
    }

    /**
     * Add duration to a resource user evaluation.
     *
     * @param int $duration
     */
    public function addDurationToResourceEvaluation(ResourceNode $node, User $user, $duration)
    {
        $this->om->startFlushSuite();

        $resUserEval = $this->getResourceUserEvaluation($node, $user);

        $evaluationDuration = $resUserEval->getDuration();
        if (is_null($resUserEval->getDuration())) {
            $evaluationDuration = $this->computeDuration($resUserEval);
        }

        $resUserEval->setDuration($evaluationDuration + $duration);

        $this->om->persist($resUserEval);
        $this->om->flush();

        $this->om->endFlushSuite();
    }

    /**
     * Compute duration for a resource user evaluation.
     *
     * @return int
     */
    public function computeDuration(ResourceUserEvaluation $resUserEval)
    {
        /** @var LogConnectResource[] $resourceLogs */
        $resourceLogs = $this->logConnectResource->findBy(['resource' => $resUserEval->getResourceNode(), 'user' => $resUserEval->getUser()]);
        $duration = 0;

        foreach ($resourceLogs as $log) {
            if ($log->getDuration()) {
                $duration += $log->getDuration();
            }
        }

        $resUserEval->setDuration($duration);

        $this->om->persist($resUserEval);
        $this->om->flush();

        return $duration;
    }
}
