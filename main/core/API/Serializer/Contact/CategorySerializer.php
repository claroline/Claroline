<?php

namespace Claroline\CoreBundle\API\Serializer\Contact;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Contact\Category;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.contact_category")
 * @DI\Tag("claroline.serializer")
 */
class CategorySerializer
{
    private $userSerializer;

    private $categoryRepo;
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

        $this->categoryRepo = $om->getRepository('Claroline\CoreBundle\Entity\Contact\Category');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @param Category $category
     *
     * @return array
     */
    public function serialize(Category $category)
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'order' => $category->getOrder(),
            'user' => $this->userSerializer->serialize($category->getUser()),
        ];
    }

    /**
     * @param array         $data
     * @param Category|null $category
     *
     * @return Category
     */
    public function deserialize(array $data, Category $category = null)
    {
        if (empty($category)) {
            $category = $this->categoryRepo->findOneBy(['id' => $data['id']]);
        }
        if (empty($category)) {
            $category = new Category();
        }
        if (isset($data['user'])) {
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['user']['id']]) : null;
            $category->setUser($user);
        }
        $this->addIfPropertyExists('name', 'setName', $data, $category);
        $this->addIfPropertyExists('order', 'setOrder', $data, $category);

        return $category;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Contact\Category';
    }

    private function addIfPropertyExists($prop, $setter, $data, Category $category)
    {
        if (isset($data[$prop])) {
            $category->$setter($data[$prop]);
        }
    }
}
