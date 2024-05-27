<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Criterion;
use Doctrine\Persistence\ObjectRepository;

class CriterionSerializer
{
    private ObjectRepository $criterionRepo;

    public function __construct(ObjectManager $om)
    {
        $this->criterionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Criterion');
    }

    public function getName(): string
    {
        return 'dropzone_criterion';
    }

    public function getClass(): string
    {
        return Criterion::class;
    }

    public function serialize(Criterion $criterion): array
    {
        return [
            'id' => $criterion->getUuid(),
            'instruction' => $criterion->getInstruction(),
        ];
    }

    public function deserialize(string $class, array $data): Criterion
    {
        $criterion = $this->criterionRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($criterion)) {
            $criterion = new Criterion();
            $criterion->setUuid($data['id']);
        }

        if (isset($data['instruction'])) {
            $criterion->setInstruction($data['instruction']);
        }

        return $criterion;
    }
}
