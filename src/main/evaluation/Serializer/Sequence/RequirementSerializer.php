<?php

namespace Claroline\EvaluationBundle\Serializer\Sequence;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\EvaluationBundle\Entity\Sequence\Requirement;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Repository\Sequence\SequenceRepository;

class RequirementSerializer
{
    use SerializerTrait;

    private SequenceRepository $sequenceRepo;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly SequenceSerializer $sequenceSerializer
    ) {
        $this->sequenceRepo = $om->getRepository(Sequence::class);
    }

    public function getClass(): string
    {
        return Requirement::class;
    }

    public function getSchema(): string
    {
        return '#/main/evaluation/sequence/requirement.json';
    }

    public function getName(): string
    {
        return 'sequence_requirement';
    }

    public function serialize(Requirement $requirement, array $options = []): array
    {
        return [
            'id' => $requirement->getUuid(),
            'status' => $requirement->getStatus(),
            'progression' => $requirement->getProgression(),
            'minScore' => $requirement->getMinScore(),
            'maxScore' => $requirement->getMaxScore(),
            'sequence' => $requirement->getSequence() ?
                $this->sequenceSerializer->serialize($requirement->getSequence(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'requiredSequence' => $requirement->getRequiredSequence() ?
                $this->sequenceSerializer->serialize($requirement->getRequiredSequence(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];
    }

    public function deserialize(array $data, Requirement $requirement, array $options = []): Requirement
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $requirement);
        } else {
            $requirement->refreshUuid();
        }

        $this->sipe('status', 'setStatus', $data, $requirement);
        $this->sipe('progression', 'setProgression', $data, $requirement);
        $this->sipe('minScore', 'setMinScore', $data, $requirement);
        $this->sipe('maxScore', 'setMaxScore', $data, $requirement);

        $sequence = isset($data['sequence']['id']) ?
            $this->sequenceRepo->findOneBy(['uuid' => $data['sequence']['id']]) :
            null;
        $requirement->setSequence($sequence);

        $requiredSequence = isset($data['requiredSequence']['id']) ?
            $this->sequenceRepo->findOneBy(['uuid' => $data['requiredSequence']['id']]) :
            null;
        $requirement->setRequiredSequence($requiredSequence);

        return $requirement;
    }
}
