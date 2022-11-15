<?php

namespace Claroline\MessageBundle\Serializer\Contact;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Contact;

class ContactSerializer
{
    private $categorySerializer;
    private $userSerializer;

    private $contactRepo;
    private $userRepo;

    public function __construct(
        CategorySerializer $categorySerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->categorySerializer = $categorySerializer;
        $this->userSerializer = $userSerializer;

        $this->contactRepo = $om->getRepository(Contact::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName(): string
    {
        return 'message_contact';
    }

    public function serialize(Contact $contact): array
    {
        return [
            'id' => $contact->getId(),
            'user' => $this->userSerializer->serialize($contact->getUser()),
            'data' => $this->userSerializer->serialize($contact->getContact()),
            'categories' => $this->getCategories($contact),
        ];
    }

    public function deserialize(array $data, Contact $contact = null): Contact
    {
        if (empty($contact)) {
            $contact = $this->contactRepo->findOneBy(['id' => $data['id']]);
        }
        if (empty($contact)) {
            $contact = new Contact();
        }
        if (isset($data['user'])) {
            /** @var User $user */
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['user']['id']]) : null;
            $contact->setUser($user);
        }
        if (isset($data['data'])) {
            /** @var User $contactUser */
            $contactUser = isset($data['data']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['data']['id']]) : null;
            $contact->setContact($contactUser);
        }
        $this->deserializeCategories($contact, $data['categories']);

        return $contact;
    }

    public function getClass(): string
    {
        return Contact::class;
    }

    private function getCategories(Contact $contact): array
    {
        $categories = [];

        foreach ($contact->getCategories() as $category) {
            $categories[] = $this->categorySerializer->serialize($category);
        }

        return $categories;
    }

    private function deserializeCategories(Contact $contact, $categoriesData): void
    {
        $contact->emptyCategories();

        foreach ($categoriesData as $categoryData) {
            $category = $this->categorySerializer->deserialize($categoryData);
            $contact->addCategory($category);
        }
    }
}
