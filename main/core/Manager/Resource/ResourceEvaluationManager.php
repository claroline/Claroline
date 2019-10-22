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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\ResourceEvaluationEvent;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourceEvaluationManager
{
    private $eventDispatcher;
    private $om;

    private $resourceUserEvaluationRepo;
    private $resourceEvaluationRepo;
    /** @var LogRepository */
    private $logRepo;
    private $logConnectResource;

    /**
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
        $this->logConnectResource = $this->om->getRepository(LogConnectResource::class);
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
        array $data = [],
        array $forced = []
    ) {
        $this->om->startFlushSuite();
        $resourceUserEvaluation = $this->getResourceUserEvaluation($node, $user);
        $evaluation = new ResourceEvaluation();
        $evaluation->setResourceUserEvaluation($resourceUserEvaluation);
        $evaluationDate = $date ? $date : new \DateTime();
        $evaluation->setDate($evaluationDate);

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
        if (isset($data['customScore'])) {
            $evaluation->setCustomScore($data['customScore']);
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
        $this->persistResourceEvaluation($evaluation);
        $this->updateResourceUserEvaluation($evaluation, $forced);
        $this->eventDispatcher->dispatch('resource_evaluation', new ResourceEvaluationEvent($resourceUserEvaluation));
        $this->om->endFlushSuite();

        return $evaluation;
    }

    public function updateResourceUserEvaluationData(
        ResourceNode $node,
        User $user = null,
        \DateTime $date = null,
        array $data = [],
        array $forced = [],
        $incAttempts = true,
        $incOpenings = false
    ) {
        $rue = $this->getResourceUserEvaluation($node, $user);
        $statusPriority = AbstractEvaluation::STATUS_PRIORITY;

        if (!empty($date)) {
            $rue->setDate($date);
        }

        if (isset($data['duration'])) {
            if (isset($forced['duration']) && $forced['duration']) {
                $rue->setDuration($data['duration']);
            } else {
                $rueDuration = $rue->getDuration() ? $rue->getDuration() : 0;
                $rueDuration += $data['duration'];
                $rue->setDuration($rueDuration);
            }
        }

        if (isset($data['score'])) {
            if (isset($forced['score']) && $forced['score']) {
                $rue->setScore($data['score']);

                if (isset($data['scoreMax'])) {
                    $rue->setScoreMax($data['scoreMax']);
                }
                if (isset($data['scoreMin'])) {
                    $rue->setScoreMin($data['scoreMin']);
                }
                if (isset($data['customScore'])) {
                    $rue->setCustomScore($data['customScore']);
                }
            } else {
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
        }

        if (isset($data['progression'])) {
            if (isset($forced['progression']) && $forced['progression']) {
                $rue->setProgression($data['progression']);

                if (isset($data['progressionMax'])) {
                    $rue->setProgressionMax($data['progressionMax']);
                }
            } else {
                $newProgresssion = empty($data['progressionMax']) ?
                    $data['progression'] :
                    $data['progression'] / $data['progressionMax'];

                $rueProgression = $rue->getProgression();
                $rueProgressionMax = $rue->getProgressionMax();
                $oldProgression = empty($rueProgressionMax) ? $rueProgression : $rueProgression / $rueProgressionMax;

                if (is_null($oldProgression) || $newProgresssion >= $oldProgression) {
                    $rue->setProgression($data['progression']);

                    if (isset($data['progressionMax'])) {
                        $rue->setProgressionMax($data['progressionMax']);
                    }
                }
            }
        }

        if (isset($data['status'])) {
            if ((isset($forced['status']) && $forced['status']) ||
                empty($rue->getStatus()) ||
                $statusPriority[$data['status']] > $statusPriority[$rue->getStatus()]
            ) {
                $rue->setStatus($data['status']);
            }
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

    private function updateResourceUserEvaluation(ResourceEvaluation $evaluation, array $forced = [], $incAttempts = true)
    {
        $rue = $evaluation->getResourceUserEvaluation();
        $rue->setDate($evaluation->getDate());

        $score = $evaluation->getScore();
        $scoreMax = $evaluation->getScoreMax();
        $scoreMin = $evaluation->getScoreMin();
        $progression = $evaluation->getProgression();
        $status = $evaluation->getStatus();
        $rueStatus = $rue->getStatus();

        $statusPriority = AbstractEvaluation::STATUS_PRIORITY;

        if (isset($forced['score']) && $forced['score']) {
            $rue->setScore($score);
            $rue->setScoreMax($scoreMax);
            $rue->setScoreMin($scoreMin);
            $rue->setCustomScore($evaluation->getCustomScore());
        } elseif (!is_null($score)) {
            $newScore = empty($scoreMax) ? $score : $score / $scoreMax;

            $rueScore = $rue->getScore();
            $rueScoreMax = $rue->getScoreMax();
            $oldScore = empty($rueScoreMax) ? $rueScore : $rueScore / $rueScoreMax;

            if (is_null($oldScore) || $newScore >= $oldScore) {
                $rue->setScore($score);
                $rue->setScoreMax($scoreMax);
                $rue->setScoreMin($evaluation->getScoreMin());
            }
        }

        if (isset($forced['progression']) && $forced['progression']) {
            $rue->setProgression($progression);
        } elseif (!is_null($progression)) {
            $rueProgression = $rue->getProgression();

            if (is_null($rueProgression) || $progression > $rueProgression) {
                $rue->setProgression($progression);
            }
        }

        if ((isset($forced['status']) && $forced['status']) ||
            empty($rueStatus) ||
            ($evaluation->isSuccessful() && !$rue->isSuccessful()) ||
            ($status && $statusPriority[$status] > $statusPriority[$rueStatus])
        ) {
            $rue->setStatus($status);
        }

        if ($incAttempts) {
            $nbAttempts = $rue->getNbAttempts() ? $rue->getNbAttempts() : 0;
            ++$nbAttempts;
            $rue->setNbAttempts($nbAttempts);
        }
        $this->persistResourceUserEvaluation($rue);
    }

    /**
     * @param ResourceNode   $node
     * @param User           $user
     * @param array          $actions
     * @param \DateTime|null $startDate
     *
     * @return Log[]
     */
    public function getLogsForResourceTracking(ResourceNode $node, User $user, array $actions, \DateTime $startDate = null)
    {
        return $this->logRepo->findLogsForResourceTracking($node, $user, $actions, $startDate);
    }

    /**
     * Add duration to a resource user evaluation.
     *
     * @param ResourceNode $node
     * @param User         $user
     * @param int          $duration
     */
    public function addDurationToResourceEvaluation(ResourceNode $node, User $user, $duration)
    {
        $this->om->startFlushSuite();

        $resUserEval = $this->getResourceUserEvaluation($node, $user);
        $evaluationDuration = is_null($resUserEval->getDuration()) ?
            $this->computeDurationForResourceEvaluation($node, $user) :
            $resUserEval->getDuration();
        $evaluationDuration += $duration;
        $resUserEval->setDuration($evaluationDuration);
        $this->om->persist($resUserEval);

        $this->om->endFlushSuite();
    }

    /**
     * Compute duration for a resource user evaluation and set it no matter the current duration.
     *
     * @param ResourceNode $node
     * @param User         $user
     * @param int          $duration
     */
    public function computeDurationForResourceEvaluation(ResourceNode $node, User $user)
    {
        $this->om->startFlushSuite();

        $resUserEval = $this->getResourceUserEvaluation($node, $user);
        $resourceLogs = $this->logConnectResource->findBy(['resource' => $node, 'user' => $user]);
        $duration = 0;

        foreach ($resourceLogs as $log) {
            if ($log->getDuration()) {
                $duration += $log->getDuration();
            }
        }
        $resUserEval->setDuration($duration);
        $this->om->persist($resUserEval);

        $this->om->endFlushSuite();

        return $duration;
    }
}
