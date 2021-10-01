<?php

namespace Claroline\ClacoFormBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntrySubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ClacoFormManager */
    private $clacoFormManager;

    private $fieldRepo;
    private $fieldChoiceCategoryRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        ClacoFormManager $clacoFormManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->clacoFormManager = $clacoFormManager;

        $this->fieldRepo = $om->getRepository(Field::class);
        $this->fieldChoiceCategoryRepo = $om->getRepository(FieldChoiceCategory::class);
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

    public function preCreate(CreateEvent $event)
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        if (empty($entry->getCreationDate())) {
            $entry->setCreationDate(new \DateTime());
            $entry->setEditionDate(new \DateTime());
        }

        // set the creator of the entry
        $user = $this->tokenStorage->getToken()->getUser();
        if (empty($entry->getUser()) && $user instanceof User) {
            $entry->setUser($user);
        }
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $this->manageCategories($entry);
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $entry->setEditionDate(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var Entry $entry */
        $entry = $event->getObject();

        $this->manageCategories($entry);
    }

    /**
     * Add/remove categories from the Entry based on fields values.
     */
    private function manageCategories(Entry $entry)
    {
        $oldCategories = $entry->getCategories();

        /** @var Field[] $fields */
        $fields = $this->fieldRepo->findBy(['clacoForm' => $entry->getClacoForm()]);

        foreach ($fields as $field) {
            /** @var FieldChoiceCategory[] $fieldsCategories */
            $fieldsCategories = $this->fieldChoiceCategoryRepo->findBy(['field' => $field]);

            $fieldValue = $entry->getFieldValue($field);
            if ($fieldValue) {
                foreach ($fieldsCategories as $fieldCategory) {
                    switch ($field->getType()) {
                        case FieldFacet::NUMBER_TYPE:
                            $isCategoryValue = floatval($fieldValue->getValue()) === floatval($fieldCategory->getValue());
                            break;
                        default:
                            $isCategoryValue = $fieldValue->getValue() === $fieldCategory->getValue();
                    }

                    if ($isCategoryValue) {
                        $entry->addCategory($fieldCategory->getCategory());
                    } elseif ($entry->hasCategory($fieldCategory->getCategory())) {
                        $entry->removeCategory($fieldCategory->getCategory());
                    }
                }
            }
        }

        $this->clacoFormManager->notifyCategoriesManagers($entry, $oldCategories, $entry->getCategories());
    }
}
