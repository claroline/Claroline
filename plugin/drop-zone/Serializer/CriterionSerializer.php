<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Criterion;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.criterion")
 * @DI\Tag("claroline.serializer")
 */
class CriterionSerializer
{
    private $criterionRepo;
    private $dropzoneRepo;

    /**
     * CriterionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->criterionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Criterion');
        $this->dropzoneRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Dropzone');
    }

    /**
     * @param Criterion $criterion
     *
     * @return array
     */
    public function serialize(Criterion $criterion)
    {
        return [
            'id' => $criterion->getUuid(),
            'instruction' => $criterion->getInstruction(),
        ];
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Criterion
     */
    public function deserialize($class, $data)
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
