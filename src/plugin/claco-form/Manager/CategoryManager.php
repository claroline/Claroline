<?php

namespace Claroline\ClacoFormBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Messenger\Message\AssignCategory;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CategoryManager
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var RouterInterface */
    private $router;
    /** @var TranslatorInterface */
    private $translator;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        MessageBusInterface $messageBus,
        RouterInterface $router,
        TranslatorInterface $translator,
        ObjectManager $om
    ) {
        $this->messageBus = $messageBus;
        $this->router = $router;
        $this->translator = $translator;
        $this->om = $om;
    }

    public function assignCategory(Category $category)
    {
        $this->messageBus->dispatch(new AssignCategory($category->getId()));
    }

    /**
     * Add/Remove category from an entry based on its fields values.
     */
    public function manageCategory(Category $category, Entry $entry)
    {
        /** @var FieldChoiceCategory[] $fieldsCategories */
        $fieldsCategories = $this->om->getRepository(FieldChoiceCategory::class)->findBy(['category' => $category]);
        if (empty($fieldsCategories)) {
            return;
        }

        $isCategoryValue = false;
        foreach ($fieldsCategories as $fieldCategory) {
            $fieldValue = $entry->getFieldValue($fieldCategory->getField());
            if ($fieldValue) {
                switch ($fieldCategory->getField()->getType()) {
                    case FieldFacet::NUMBER_TYPE:
                        $isCategoryValue = floatval($fieldValue->getValue()) === floatval($fieldCategory->getValue());
                        break;
                    default:
                        $isCategoryValue = $fieldValue->getValue() === $fieldCategory->getValue();
                }

                if ($isCategoryValue) {
                    break;
                }
            }
        }

        if ($isCategoryValue && !$entry->hasCategory($category)) {
            // entry is newly added to the category
            $entry->addCategory($category);
            $this->om->persist($entry);
            $this->om->flush();

            $this->notifyNewEntry($entry, $category);
        } elseif (!$isCategoryValue && $entry->hasCategory($category)) {
            // entry is newly removed from the category
            $entry->removeCategory($category);
            $this->om->persist($entry);
            $this->om->flush();

            $this->notifyRemovedEntry($entry, $category);
        }
    }

    public function notifyNewEntry(Entry $entry, Category $addedCategory)
    {
        if (!$addedCategory->getNotifyAddition() || empty($addedCategory->getManagers())) {
            return;
        }

        $resourceNode = $entry->getClacoForm()->getResourceNode();
        $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
            '#/desktop/resources/'.$resourceNode->getSlug().'/entries/'.$entry->getUuid();

        $object = $this->translator->trans('entry_addition_in_category', [
            '%name%' => $addedCategory->getName(),
            '%clacoform%' => $resourceNode->getName(),
        ], 'clacoform');

        $content = $this->translator->trans('entry_addition_in_category_msg', [
            '%title%' => $entry->getTitle(),
            '%category%' => $addedCategory->getName(),
            '%clacoform%' => $resourceNode->getName(),
            '%url%' => $url,
        ], 'clacoform');

        $this->messageBus->dispatch(
            new SendMessage($content, $object, array_map(function (User $user) {
                return $user->getId();
            }, $addedCategory->getManagers()))
        );
    }

    /**
     * Notify managers when an entry is removed from a category.
     */
    public function notifyRemovedEntry(Entry $entry, Category $removedCategory)
    {
        if (!$removedCategory->getNotifyRemoval() || empty($removedCategory->getManagers())) {
            return;
        }

        $resourceNode = $entry->getClacoForm()->getResourceNode();

        $object = $this->translator->trans('entry_removal_from_category', [
            '%name%' => $removedCategory->getName(),
            '%clacoform%' => $resourceNode->getName(),
        ], 'clacoform');

        $content = $this->translator->trans('entry_removal_from_category_msg', [
            '%title%' => $entry->getTitle(),
            '%category%' => $removedCategory->getName(),
            '%clacoform%' => $resourceNode->getName(),
        ], 'clacoform');

        $this->messageBus->dispatch(
            new SendMessage($content, $object, array_map(function (User $user) {
                return $user->getId();
            }, $removedCategory->getManagers()))
        );
    }

    /**
     * Notify managers when an entry of their categories is edited.
     */
    public function notifyEditedEntry(Entry $entry, array $categories)
    {
        if (empty($categories)) {
            return;
        }

        $resourceNode = $entry->getClacoForm()->getResourceNode();
        $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
            '#/desktop/resources/'.$resourceNode->getSlug().'/entries/'.$entry->getUuid();

        foreach ($categories as $category) {
            if (!$category->getNotifyEdition() || empty($category->getManagers())) {
                continue;
            }

            $object = $this->translator->trans('entry_edition_in_category', [
                '%name%' => $category->getName(),
                '%clacoform%' => $resourceNode->getName(),
            ], 'clacoform');

            $content = $this->translator->trans('entry_edition_in_category_msg', [
                '%title%' => $entry->getTitle(),
                '%category%' => $category->getName(),
                '%clacoform%' => $resourceNode->getName(),
                '%url%' => $url,
            ], 'clacoform');

            $this->messageBus->dispatch(
                new SendMessage($content, $object, array_map(function (User $user) {
                    return $user->getId();
                }, $category->getManagers()))
            );
        }
    }
}
