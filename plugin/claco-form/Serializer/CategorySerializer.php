<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.category")
 * @DI\Tag("claroline.serializer")
 */
class CategorySerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    private $clacoFormRepo;
    private $userRepo;

    /**
     * CategorySerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer" = @DI\Inject("claroline.serializer.user"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param UserSerializer $userSerializer
     * @param ObjectManager  $om
     */
    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;

        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * Serializes a Category entity for the JSON api.
     *
     * @param Category $category - the category to serialize
     * @param array    $options  - a list of serialization options
     *
     * @return array - the serialized representation of the category
     */
    public function serialize(Category $category, array $options = [])
    {
        $serialized = [
            'id' => $category->getUuid(),
            'name' => $category->getName(),
            'details' => $category->getDetails(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager);
                }, $category->getManagers()),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array    $data
     * @param Category $category
     *
     * @return Category
     */
    public function deserialize($data, Category $category)
    {
        $this->sipe('name', 'setName', $data, $category);
        $this->sipe('details', 'setDetails', $data, $category);

        if (isset($data['clacoForm']['id'])) {
            $clacoForm = $this->clacoFormRepo->findOneBy(['uuid' => $data['clacoForm']['id']]);

            if (!empty($clacoForm)) {
                $category->setClacoForm($clacoForm);
            }
        }
        $category->emptyManagers();

        if (isset($data['managers'])) {
            foreach ($data['managers'] as $managerData) {
                $manager = $this->userRepo->findOneBy(['username' => $managerData['username']]);

                if (!empty($manager)) {
                    $category->addManager($manager);
                }
            }
        }

        return $category;
    }
}
