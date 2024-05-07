<?php

namespace Claroline\ClacoFormBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Manager\CategoryManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntrySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly CategoryManager $categoryManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Entry::class) => 'preCreate',
            Crud::getEventName('create', 'post', Entry::class) => 'postCreate',
            Crud::getEventName('update', 'pre', Entry::class) => 'preUpdate',
            Crud::getEventName('update', 'post', Entry::class) => 'postUpdate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        if (empty($entry->getCreationDate())) {
            $entry->setCreationDate(new \DateTime());
            $entry->setEditionDate(new \DateTime());
        }

        // set the creator of the entry
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $entry->setUser($user);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $this->manageCategories($entry);
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $entry->setEditionDate(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $this->manageCategories($entry);
    }

    /**
     * Add/remove categories from the Entry based on fields values.
     */
    private function manageCategories(Entry $entry): void
    {
        $oldCategories = $entry->getCategories();

        $categories = $this->om->getRepository(Category::class)->findAutoCategories($entry->getClacoForm());
        foreach ($categories as $category) {
            $this->categoryManager->manageCategory($category, $entry);
        }

        // notify category managers the entry has been edited
        // NB. we filter newly added categories because there is another notification for that
        $editedCategories = [];
        foreach ($oldCategories as $oldCategory) {
            if (in_array($oldCategory, $entry->getCategories())) {
                $editedCategories[] = $oldCategory;
            }
        }

        if (!empty($editedCategories)) {
            $this->categoryManager->notifyEditedEntry($entry, $editedCategories);
        }
    }
}
