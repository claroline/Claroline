<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ProjectBundle\Entity\EvaluationCriteria;
use Doctrine\Persistence\ObjectRepository;

class EvaluationCriteriaSerializer
{
    private ObjectRepository $criteriaRepo;

    public function __construct(ObjectManager $om)
    {
        $this->criteriaRepo = $om->getRepository(EvaluationCriteria::class);
    }

    public function getName(): string
    {
        return 'project_evaluation_criteria';
    }

    public function getClass(): string
    {
        return EvaluationCriteria::class;
    }

    public function serialize(EvaluationCriteria $criteria): array
    {
        return [
            'id' => $criteria->getUuid(),
            'label' => $criteria->getLabel(),
            'description' => $criteria->getDescription(),
            'scoreMax' => $criteria->getScoreMax(),
            'position' => $criteria->getPosition(),
        ];
    }

    public function deserialize(array $data): EvaluationCriteria
    {
        $criteria = null;

        if (isset($data['id'])) {
            $criteria = $this->criteriaRepo->findOneBy(['uuid' => $data['id']]);
        }

        if (empty($criteria)) {
            $criteria = new EvaluationCriteria();
            if (isset($data['id'])) {
                $criteria->setUuid($data['id']);
            }
        }

        if (isset($data['label'])) {
            $criteria->setLabel($data['label']);
        }
        if (array_key_exists('description', $data)) {
            $criteria->setDescription($data['description']);
        }
        if (isset($data['scoreMax'])) {
            $criteria->setScoreMax($data['scoreMax']);
        }
        if (isset($data['position'])) {
            $criteria->setPosition($data['position']);
        }

        return $criteria;
    }
}
