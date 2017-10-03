<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\CoreBundle\API\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.category")
 * @DI\Tag("claroline.serializer")
 */
class CategorySerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * CategorySerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param UserSerializer $userSerializer
     */
    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
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
            'id' => $category->getId(),
            'name' => $category->getName(),
            'details' => $category->getDetails(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager);
                }, $category->getManagers()),
            ]);
        }

        return $serialized;
    }
}
