<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Keyword;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.keyword")
 * @DI\Tag("claroline.serializer")
 */
class KeywordSerializer
{
    use SerializerTrait;

    private $clacoFormRepo;

    /**
     * KeywordSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
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
     * @param array   $data
     * @param Keyword $keyword
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
