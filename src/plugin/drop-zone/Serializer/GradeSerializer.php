<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Grade;
use Doctrine\Persistence\ObjectRepository;

class GradeSerializer
{
    private ObjectRepository $correctionRepo;
    private ObjectRepository $criterionRepo;
    private ObjectRepository $gradeRepo;

    public function __construct(ObjectManager $om)
    {
        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
        $this->criterionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Criterion');
        $this->gradeRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Grade');
    }

    public function getName(): string
    {
        return 'dropzone_grade';
    }

    public function getClass(): string
    {
        return Grade::class;
    }

    public function serialize(Grade $grade): array
    {
        return [
            'id' => $grade->getUuid(),
            'value' => $grade->getValue(),
            'correction' => $grade->getCorrection()->getUuid(),
            'criterion' => $grade->getCriterion()->getUuid(),
        ];
    }

    public function deserialize(string $class, array $data): Grade
    {
        $grade = $this->gradeRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($grade)) {
            $grade = new Grade();
            $grade->setUuid($data['id']);
            $correction = $data['correction'] instanceof Correction ?
                $data['correction'] :
                $this->correctionRepo->findOneBy(['uuid' => $data['correction']]);
            $grade->setCorrection($correction);
            $criterion = $this->criterionRepo->findOneBy(['uuid' => $data['criterion']]);
            $grade->setCriterion($criterion);
        }
        if (isset($data['value'])) {
            $grade->setValue($data['value']);
        }

        return $grade;
    }
}
