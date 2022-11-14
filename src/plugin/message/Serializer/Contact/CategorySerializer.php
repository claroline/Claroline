<?php

namespace Claroline\MessageBundle\Serializer\Contact;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Category;

class CategorySerializer
{
    private $userSerializer;

    private $categoryRepo;
    private $userRepo;

    /**
     * CategorySerializer constructor.
     */
    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;

        $this->categoryRepo = $om->getRepository(Category::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    /**
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

    public function getName()
    {
        return 'message_contact_category';
    }

    /**
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
            /** @var User $user */
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['user']['id']]) : null;
            $category->setUser($user);
        }
        $this->addIfPropertyExists('name', 'setName', $data, $category);
        $this->addIfPropertyExists('order', 'setOrder', $data, $category);

        return $category;
    }

    public function getClass()
    {
        return Category::class;
    }

    private function addIfPropertyExists($prop, $setter, $data, Category $category)
    {
        if (isset($data[$prop])) {
            $category->$setter($data[$prop]);
        }
    }
}
