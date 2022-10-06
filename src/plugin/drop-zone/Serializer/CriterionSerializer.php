<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Criterion;

class CriterionSerializer
{
    private $criterionRepo;
    private $dropzoneRepo;

    public function __construct(ObjectManager $om)
    {
        $this->criterionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Criterion');
        $this->dropzoneRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Dropzone');
    }

    public function getName()
    {
        return 'dropzone_criterion';
    }

    /**
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
