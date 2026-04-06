<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ProjectBundle\Entity\CriteriaScore;
use Claroline\ProjectBundle\Entity\EvaluationCriteria;
use Doctrine\Persistence\ObjectRepository;

class CriteriaScoreSerializer
{
    private ObjectRepository $criteriaScoreRepo;
    private ObjectRepository $criteriaRepo;

    public function __construct(ObjectManager $om)
    {
        $this->criteriaScoreRepo = $om->getRepository(CriteriaScore::class);
        $this->criteriaRepo = $om->getRepository(EvaluationCriteria::class);
    }

    public function getName(): string
    {
        return 'project_criteria_score';
    }

    public function getClass(): string
    {
        return CriteriaScore::class;
    }

    public function serialize(CriteriaScore $criteriaScore): array
    {
        return [
            'id' => $criteriaScore->getUuid(),
            'score' => $criteriaScore->getScore(),
            'comment' => $criteriaScore->getComment(),
            'criteriaId' => $criteriaScore->getCriteria() ? $criteriaScore->getCriteria()->getUuid() : null,
        ];
    }

    public function deserialize(array $data): CriteriaScore
    {
        $criteriaScore = null;

        if (isset($data['id'])) {
            $criteriaScore = $this->criteriaScoreRepo->findOneBy(['uuid' => $data['id']]);
        }

        if (empty($criteriaScore)) {
            $criteriaScore = new CriteriaScore();
            if (isset($data['id'])) {
                $criteriaScore->setUuid($data['id']);
            }
        }

        if (isset($data['score'])) {
            $criteriaScore->setScore($data['score']);
        }
        if (array_key_exists('comment', $data)) {
            $criteriaScore->setComment($data['comment']);
        }
        if (isset($data['criteriaId'])) {
            $criteria = $this->criteriaRepo->findOneBy(['uuid' => $data['criteriaId']]);
            if ($criteria) {
                $criteriaScore->setCriteria($criteria);
            }
        }

        return $criteriaScore;
    }
}
