<?php

namespace Claroline\EvaluationBundle\Serializer\UserEvaluation;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceEvaluationSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer
    ) {
    }

    public function getName(): string
    {
        return 'workspace_evaluation';
    }

    public function getClass(): string
    {
        return WorkspaceEvaluation::class;
    }

    public function getSchema(): string
    {
        return '#/main/evaluation/workspace-evaluation.json';
    }

    public function getSamples(): string
    {
        return '#/main/evaluation/workspace_evaluation';
    }

    public function serialize(WorkspaceEvaluation $evaluation, ?array $options = []): array
    {
        $progression = $evaluation->getProgression();
        if ($progression) {
            $progression = round($progression, EvaluationOptions::PROGRESSION_PRECISION);
        }

        $serialized = [
            'id' => $evaluation->getUuid(),
            'meta' => [
                'archived' => $evaluation->isArchived(),
                'archivedAt' => DateNormalizer::normalize($evaluation->getArchivedAt()),
            ],
            'lastActivityAt' => DateNormalizer::normalize($evaluation->getLastActivityAt()),
            'startedAt' => DateNormalizer::normalize($evaluation->getStartedAt()),
            'endedAt' => DateNormalizer::normalize($evaluation->getEndedAt()),
            'status' => $evaluation->getStatus(),
            'duration' => $evaluation->getDuration(),
            'progression' => $progression,
            'certified' => $evaluation->isCertified(),
            'estimatedDuration' => $evaluation->getEstimatedDuration() * 60,
        ];

        // evaluation has a score, expose it
        if ($evaluation->getScoreMax()) {
            $serialized['rawScore'] = [
                'current' => $evaluation->getScore(),
                'total' => $evaluation->getScoreMax(),
            ];

            $score = $evaluation->getScore();
            $total = $evaluation->getScoreMax();
            if ($score && $evaluation->getWorkspace() && $evaluation->getWorkspace()->getScoreTotal()) {
                $score = ($evaluation->getScore() / $evaluation->getScoreMax()) * $evaluation->getWorkspace()->getScoreTotal();
                $total = $evaluation->getWorkspace()->getScoreTotal();
            }

            if ($score) {
                $score = round($score, EvaluationOptions::SCORE_PRECISION);
            }

            $serialized['displayScore'] = [
                'current' => $score,
                'total' => $total,
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $evaluation);
                $serialized['permissions'] = [
                    'open' => $isAdmin || $this->authorization->isGranted('OPEN', $evaluation),
                    'administrate' => $isAdmin,
                ];
            }

            $serialized['user'] = null;
            if ($evaluation->getUser()) {
                $serialized['user'] = $this->userSerializer->serialize($evaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }

            $serialized['workspace'] = null;
            if ($evaluation->getWorkspace()) {
                $serialized['workspace'] = $this->workspaceSerializer->serialize($evaluation->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, WorkspaceEvaluation $evaluation, ?array $options = []): WorkspaceEvaluation
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $evaluation);
        } else {
            $evaluation->refreshUuid();
        }

        $this->sipe('meta.archived', 'setArchived', $data, $evaluation);
        $this->sipe('status', 'setStatus', $data, $evaluation);
        $this->sipe('duration', 'setDuration', $data, $evaluation);
        $this->sipe('progression', 'setProgression', $data, $evaluation);
        $this->sipe('certified', 'setCertified', $data, $evaluation);
        $this->sipe('duration', 'setDuration', $data, $evaluation);
        $this->sipe('rawScore.current', 'setScore', $data, $evaluation);
        $this->sipe('rawScore.total', 'setScoreMax', $data, $evaluation);

        if (isset($data['lastActivityAt'])) {
            $evaluation->setLastActivityAt(DateNormalizer::denormalize($data['lastActivityAt']));
        }
        if (isset($data['startedAt'])) {
            $evaluation->setStartedAt(DateNormalizer::denormalize($data['startedAt']));
        }
        if (isset($data['endedAt'])) {
            $evaluation->setEndedAt(DateNormalizer::denormalize($data['endedAt']));
        }

        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getObject($data['user'], User::class, User::getIdentifiers());
            if (!empty($user)) {
                $evaluation->setUser($user);
            }
        }

        if (isset($data['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getObject($data['workspace'], Workspace::class, Workspace::getIdentifiers());
            if ($workspace) {
                $evaluation->setWorkspace($workspace);
            }
        }

        return $evaluation;
    }
}
