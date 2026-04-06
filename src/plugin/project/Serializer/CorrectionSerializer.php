<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\CriteriaScore;
use Doctrine\Persistence\ObjectRepository;

class CorrectionSerializer
{
    private ObjectRepository $correctionRepo;

    public function __construct(
        private readonly CriteriaScoreSerializer $criteriaScoreSerializer,
        private readonly UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->correctionRepo = $om->getRepository(Correction::class);
    }

    public function getName(): string
    {
        return 'project_correction';
    }

    public function getClass(): string
    {
        return Correction::class;
    }

    public function serialize(Correction $correction): array
    {
        return [
            'id' => $correction->getUuid(),
            'status' => $correction->getStatus(),
            'score' => $correction->getScore(),
            'comment' => $correction->getComment(),
            'startDate' => DateNormalizer::normalize($correction->getStartDate()),
            'lastEditionDate' => DateNormalizer::normalize($correction->getLastEditionDate()),
            'submissionDate' => DateNormalizer::normalize($correction->getSubmissionDate()),
            'submission' => $correction->getSubmission() ? $correction->getSubmission()->getUuid() : null,
            'milestone' => $correction->getMilestone() ? $correction->getMilestone()->getUuid() : null,
            'user' => $correction->getUser() ?
                $this->userSerializer->serialize($correction->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'corrector' => $correction->getCorrector() ?
                $this->userSerializer->serialize($correction->getCorrector(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'criteriaScores' => array_map(function (CriteriaScore $criteriaScore) {
                return $this->criteriaScoreSerializer->serialize($criteriaScore);
            }, $correction->getCriteriaScores()),
        ];
    }

    public function deserialize(array $data, ?Correction $correction = null): Correction
    {
        if (empty($correction)) {
            if (isset($data['id'])) {
                $correction = $this->correctionRepo->findOneBy(['uuid' => $data['id']]);
            }
        }
        $correction = $correction ?: new Correction();

        if (isset($data['id'])) {
            $correction->setUuid($data['id']);
        }
        if (isset($data['score'])) {
            $correction->setScore($data['score']);
        }
        if (array_key_exists('comment', $data)) {
            $correction->setComment($data['comment']);
        }

        if (isset($data['criteriaScores'])) {
            $this->deserializeCriteriaScores($correction, $data['criteriaScores']);
        }

        $correction->setLastEditionDate(new \DateTime());

        return $correction;
    }

    private function deserializeCriteriaScores(Correction $correction, array $criteriaScoresData): void
    {
        $oldScores = $correction->getCriteriaScores();
        $newUuids = [];

        foreach ($criteriaScoresData as $scoreData) {
            $criteriaScore = $this->criteriaScoreSerializer->deserialize($scoreData);
            $criteriaScore->setCorrection($correction);
            $correction->addCriteriaScore($criteriaScore);
            $newUuids[] = $criteriaScore->getUuid();
        }

        foreach ($oldScores as $oldScore) {
            if (!in_array($oldScore->getUuid(), $newUuids)) {
                $correction->removeCriteriaScore($oldScore);
            }
        }
    }
}
