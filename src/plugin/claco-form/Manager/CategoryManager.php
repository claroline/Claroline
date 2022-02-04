<?php

namespace Claroline\ClacoFormBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Messenger\Message\AssignCategory;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
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
        $this->messageBus->dispatch(new AssignCategory($category->getUuid()));
    }

    public function manageCategory(Category $category, Entry $entry)
    {
        $newCategories = [];
        $oldCategories = [];

        /** @var FieldChoiceCategory[] $fieldsCategories */
        $fieldsCategories = $this->om->getRepository(FieldChoiceCategory::class)->findBy(['category' => $category]);
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
                    $entry->addCategory($fieldCategory->getCategory());
                    $newCategories = [$fieldCategory->getCategory()];
                    $oldCategories = [];
                } elseif ($entry->hasCategory($fieldCategory->getCategory())) {
                    $entry->removeCategory($fieldCategory->getCategory());
                    $newCategories = [];
                    $oldCategories = [$fieldCategory->getCategory()];
                }
            }
        }

        if (!empty($newCategories) || !empty($oldCategories)) {
            $this->om->persist($entry);
            $this->om->flush();

            $this->notifyCategoriesManagers($entry, $oldCategories, $newCategories);
        }
    }

    public function notifyCategoriesManagers(Entry $entry, array $oldCategories = [], array $currentCategories = [])
    {
        $removedCategories = [];
        $editedCategories = [];
        $addedCategories = [];
        $node = $entry->getClacoForm()->getResourceNode();
        $clacoFormName = $node->getName();
        $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
            '#/desktop/resources/'.$node->getSlug().'/entries/'.$entry->getUuid();

        foreach ($oldCategories as $category) {
            if (in_array($category, $currentCategories)) {
                $editedCategories[$category->getId()] = $category;
            } else {
                $removedCategories[$category->getId()] = $category;
            }
        }
        foreach ($currentCategories as $category) {
            if (!in_array($category, $oldCategories)) {
                $addedCategories[$category->getId()] = $category;
            }
        }
        foreach ($removedCategories as $category) {
            if ($category->getNotifyRemoval()) {
                $managers = $category->getManagers();

                if (count($managers) > 0) {
                    $object = $this->translator->trans(
                        'entry_removal_from_category',
                        ['%name%' => $category->getName(), '%clacoform%' => $clacoFormName],
                        'clacoform'
                    );
                    $content = $this->translator->trans(
                        'entry_removal_from_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName(), '%clacoform%' => $clacoFormName],
                        'clacoform'
                    );
                    $this->messageBus->dispatch(new SendMessage($content, $object, $managers));
                }
            }
        }
        foreach ($editedCategories as $category) {
            if ($category->getNotifyEdition()) {
                $managers = $category->getManagers();

                if (count($managers) > 0) {
                    $object = $this->translator->trans(
                        'entry_edition_in_category',
                        ['%name%' => $category->getName(), '%clacoform%' => $clacoFormName],
                        'clacoform'
                    );
                    $content = $this->translator->trans(
                        'entry_edition_in_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName(), '%clacoform%' => $clacoFormName, '%url%' => $url],
                        'clacoform'
                    );
                    $this->messageBus->dispatch(new SendMessage($content, $object, $managers));
                }
            }
        }
        foreach ($addedCategories as $category) {
            if ($category->getNotifyAddition()) {
                $managers = $category->getManagers();

                if (count($managers) > 0) {
                    $object = $this->translator->trans(
                        'entry_addition_in_category',
                        ['%name%' => $category->getName(), '%clacoform%' => $clacoFormName],
                        'clacoform'
                    );
                    $content = $this->translator->trans(
                        'entry_addition_in_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName(), '%clacoform%' => $clacoFormName, '%url%' => $url],
                        'clacoform'
                    );
                    $this->messageBus->dispatch(new SendMessage($content, $object, $managers));
                }
            }
        }
    }
}
