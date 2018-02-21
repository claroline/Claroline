<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Grade;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.grade")
 * @DI\Tag("claroline.serializer")
 */
class GradeSerializer
{
    private $correctionRepo;
    private $criterionRepo;
    private $gradeRepo;

    /**
     * GradeSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
        $this->criterionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Criterion');
        $this->gradeRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Grade');
    }

    /**
     * @param Grade $grade
     *
     * @return array
     */
    public function serialize(Grade $grade)
    {
        return [
            'id' => $grade->getUuid(),
            'value' => $grade->getValue(),
            'correction' => $grade->getCorrection()->getUuid(),
            'criterion' => $grade->getCriterion()->getUuid(),
        ];
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Grade
     */
    public function deserialize($class, $data)
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
