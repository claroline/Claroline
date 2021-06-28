<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Keyword;

class KeywordSerializer
{
    use SerializerTrait;

    private $clacoFormRepo;

    /**
     * KeywordSerializer constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
    }

    public function getName()
    {
        return 'clacoform_keyword';
    }

    /**
     * Serializes a Keyword entity for the JSON api.
     *
     * @param Keyword $keyword - the keyword to serialize
     * @param array   $options - a list of serialization options
     *
     * @return array - the serialized representation of the keyword
     */
    public function serialize(Keyword $keyword, array $options = [])
    {
        $serialized = [
            'id' => $keyword->getUuid(),
            'name' => $keyword->getName(),
        ];

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return Keyword
     */
    public function deserialize($data, Keyword $keyword)
    {
        $this->sipe('name', 'setName', $data, $keyword);

        if (isset($data['clacoForm']['id'])) {
            $clacoForm = $this->clacoFormRepo->findOneBy(['uuid' => $data['clacoForm']['id']]);

            if (!empty($clacoForm)) {
                $keyword->setClacoForm($clacoForm);
            }
        }

        return $keyword;
    }
}
