<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Repository\EntryRepository;
use Claroline\ClacoFormBundle\Repository\FieldValueRepository;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClacoFormManager
{
    private EntryRepository $entryRepo;
    private FieldValueRepository $fieldValueRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly string $filesDir,
        private readonly ObjectManager $om,
        private readonly CategoryManager $categoryManager
    ) {
        $this->entryRepo = $om->getRepository(Entry::class);
        $this->fieldValueRepo = $om->getRepository(FieldValue::class);
    }

    public function persistEntry(Entry $entry): void
    {
        $this->om->persist($entry);
        $this->om->flush();
    }

    public function getRandomEntryId(ClacoForm $clacoForm): ?string
    {
        $entryId = null;
        $entries = $this->getRandomEntries($clacoForm);
        $count = count($entries);

        if ($count > 0) {
            $randomIndex = rand(0, $count - 1);
            $entryId = $entries[$randomIndex]->getUuid();
        }

        return $entryId;
    }

    public function getRandomEntries(ClacoForm $clacoForm): array
    {
        $categoriesIds = $clacoForm->getRandomCategories();
        $start = $clacoForm->getRandomStartDate();
        $startDate = empty($start) ? null : new \DateTime($start);
        $end = $clacoForm->getRandomEndDate();
        $endDate = empty($end) ? null : new \DateTime($end);

        if (!is_null($endDate)) {
            $endDate->setTime(23, 59, 59);
        }

        return count($categoriesIds) > 0 ?
            $this->getPublishedEntriesByCategoriesAndDates($clacoForm, $categoriesIds, $startDate, $endDate) :
            $this->getPublishedEntriesByDates($clacoForm, $startDate, $endDate);
    }

    public function getAllUsedCountriesCodes(ClacoForm $clacoForm): array
    {
        $values = [];
        $fieldValues = $this->getFieldValuesByType($clacoForm, FieldFacet::COUNTRY_TYPE);

        foreach ($fieldValues as $fieldValue) {
            if (!empty($fieldValue->getFieldFacetValue() && !empty($fieldValue->getFieldFacetValue()))) {
                $value = $fieldValue->getFieldFacetValue()->getValue();

                if (!empty($value) && !in_array($value, $values)) {
                    $values[] = $value;
                }
            }
        }

        return sort($values) ? $values : [];
    }

    public function changeEntryStatus(Entry $entry): Entry
    {
        $status = $entry->getStatus();

        switch ($status) {
            case Entry::PENDING:
                $entry->setPublicationDate(new \DateTime());
                // no break
            case Entry::UNPUBLISHED:
                $entry->setStatus(Entry::PUBLISHED);
                break;
            case Entry::PUBLISHED:
                $entry->setStatus(Entry::UNPUBLISHED);
                break;
        }
        $this->persistEntry($entry);

        $this->categoryManager->notifyEditedEntry($entry, $entry->getCategories());

        return $entry;
    }

    public function changeEntriesStatus(array $entries, $status): array
    {
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            if (Entry::PUBLISHED === $status) {
                $entry->setPublicationDate(new \DateTime());
            }
            $entry->setStatus($status);
            $this->persistEntry($entry);

            $this->categoryManager->notifyEditedEntry($entry, $entry->getCategories());
        }
        $this->om->endFlushSuite();

        return $entries;
    }

    public function switchEntryLock(Entry $entry): Entry
    {
        $locked = $entry->isLocked();
        $entry->setLocked(!$locked);
        $this->persistEntry($entry);

        $this->categoryManager->notifyEditedEntry($entry, $entry->getCategories());

        return $entry;
    }

    public function switchEntriesLock(array $entries, $locked): array
    {
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            $entry->setLocked($locked);
            $this->persistEntry($entry);

            $this->categoryManager->notifyEditedEntry($entry, $entry->getCategories());
        }
        $this->om->endFlushSuite();

        return $entries;
    }

    public function changeEntryOwner(Entry $entry, User $user): Entry
    {
        $entry->setUser($user);
        $this->persistEntry($entry);

        return $entry;
    }

    public function countUserEntries(ClacoForm $clacoForm, ?User $user = null): int
    {
        if (!$user) {
            return 0;
        }

        return $this->entryRepo->count(['clacoForm' => $clacoForm, 'user' => $user]);
    }

    public function copyClacoForm(ClacoForm $clacoForm, ClacoForm $newClacoForm): ClacoForm
    {
        $categoryLinks = [];
        $fieldLinks = [];
        $fieldFacetLinks = [];
        $categories = $clacoForm->getCategories();
        $fields = $clacoForm->getFields();
        $entries = $this->getAllEntries($clacoForm);

        foreach ($categories as $category) {
            $newCategory = $this->copyCategory($newClacoForm, $category);
            $categoryLinks[$category->getId()] = $newCategory;
        }
        foreach ($fields as $field) {
            $links = $this->copyField($newClacoForm, $field, $categoryLinks);

            foreach ($links['fields'] as $key => $value) {
                $fieldLinks[$key] = $value;
            }
            foreach ($links['fieldFacets'] as $key => $value) {
                $fieldFacetLinks[$key] = $value;
            }
        }
        foreach ($entries as $entry) {
            $this->copyEntry($newClacoForm, $entry, $categoryLinks, $fieldLinks, $fieldFacetLinks);
        }
        $template = $clacoForm->getTemplate();

        if ($template) {
            foreach ($fieldLinks as $key => $value) {
                $template = str_replace("%field_$key%", '%field_'.$value->getUuid().'%', $template);
            }
            $newClacoForm->setTemplate($template);
        }

        return $newClacoForm;
    }

    private function copyCategory(ClacoForm $newClacoForm, Category $category): Category
    {
        $newCategory = new Category();
        $newCategory->setClacoForm($newClacoForm);
        $newCategory->setName($category->getName());
        $newCategory->setDetails($category->getDetails());
        $managers = $category->getManagers();

        foreach ($managers as $manager) {
            $newCategory->addManager($manager);
        }
        $this->om->persist($newCategory);

        return $newCategory;
    }

    private function copyField(ClacoForm $newClacoForm, Field $field, array $categoryLinks): array
    {
        $links = [
            'fields' => [],
            'fieldFacets' => [],
            'fieldFacetChoices' => [],
        ];

        $fieldFacet = $field->getFieldFacet();

        $newField = new Field();
        $newField->setClacoForm($newClacoForm);
        $newField->setLabel($field->getLabel());
        $newField->setType($field->getType());
        $newField->setOrder($field->getOrder());
        $newField->setRequired($field->isRequired());
        $newField->setConfidentiality($field->getConfidentiality());
        $newField->setLocked($field->isLocked());
        $newField->setLockedEditionOnly($field->getLockedEditionOnly());
        $newField->setOptions($field->getOptions());
        $newField->setHelp($field->getHelp());

        $links['fieldFacets'][$fieldFacet->getId()] = $newField->getFieldFacet();

        $this->om->persist($newField);
        $links['fields'][$field->getUuid()] = $newField;

        $fieldFacetChoices = $fieldFacet->getFieldFacetChoices()->toArray();

        foreach ($fieldFacetChoices as $fieldFacetChoice) {
            $newFieldFacetChoice = new FieldFacetChoice();
            $newFieldFacetChoice->setFieldFacet($newField->getFieldFacet());
            $newFieldFacetChoice->setLabel($fieldFacetChoice->getLabel());
            $newFieldFacetChoice->setPosition($fieldFacetChoice->getPosition());
            $this->om->persist($newFieldFacetChoice);
            $links['fieldFacetChoices'][$fieldFacetChoice->getId()] = $newFieldFacetChoice;
        }
        foreach ($fieldFacetChoices as $fieldFacetChoice) {
            $parent = $fieldFacetChoice->getParent();

            if (!empty($parent)) {
                $newFieldFacetChoice = $links['fieldFacetChoices'][$fieldFacetChoice->getId()];
                $newParent = $links['fieldFacetChoices'][$parent->getId()];
                $newFieldFacetChoice->setParent($newParent);
                $this->om->persist($newFieldFacetChoice);
            }
        }
        $fieldChoiceCategories = $field->getFieldChoiceCategories();

        foreach ($fieldChoiceCategories as $fieldChoiceCategory) {
            $categoryId = $fieldChoiceCategory->getCategory()->getId();

            if (isset($categoryLinks[$categoryId])) {
                $newFieldChoiceCategory = new FieldChoiceCategory();
                $newFieldChoiceCategory->setField($newField);
                $newFieldChoiceCategory->setValue($fieldChoiceCategory->getValue());
                $newFieldChoiceCategory->setCategory($categoryLinks[$categoryId]);
                $this->om->persist($newFieldChoiceCategory);
            }
        }

        return $links;
    }

    private function copyEntry(
        ClacoForm $newClacoForm,
        Entry $entry,
        array $categoryLinks,
        array $fieldLinks,
        array $fieldFacetLinks
    ): void {
        $categories = $entry->getCategories();
        $fieldValues = $entry->getFieldValues();
        $newEntry = new Entry();
        $newEntry->setClacoForm($newClacoForm);
        $newEntry->setTitle($entry->getTitle());
        $newEntry->setUser($entry->getUser());
        $newEntry->setCreationDate($entry->getCreationDate());
        $newEntry->setEditionDate($entry->getEditionDate());
        $newEntry->setPublicationDate($entry->getPublicationDate());
        $newEntry->setStatus($entry->getStatus());

        foreach ($categories as $category) {
            if (isset($categoryLinks[$category->getId()])) {
                $newEntry->addCategory($categoryLinks[$category->getId()]);
            }
        }
        $this->om->persist($newEntry);

        foreach ($fieldValues as $fieldValue) {
            $this->copyFieldValue($newEntry, $fieldValue, $fieldLinks, $fieldFacetLinks);
        }
    }

    private function copyFieldValue(Entry $newEntry, FieldValue $fieldValue, array $fieldLinks, array $fieldFacetLinks): void
    {
        $fieldId = $fieldValue->getField()->getUuid();
        $fieldFacetValue = $fieldValue->getFieldFacetValue();
        $fieldFacetId = $fieldFacetValue->getFieldFacet()->getId();

        if (isset($fieldLinks[$fieldId]) && isset($fieldFacetLinks[$fieldFacetId])) {
            $newFieldFacetValue = new FieldFacetValue();
            $newFieldFacetValue->setFieldFacet($fieldFacetLinks[$fieldFacetId]);
            $newFieldFacetValue->setUser($fieldFacetValue->getUser());
            $newFieldFacetValue->setValue($fieldFacetValue->getValue());
            $this->om->persist($newFieldFacetValue);

            $newFieldValue = new FieldValue();
            $newFieldValue->setEntry($newEntry);
            $newFieldValue->setField($fieldLinks[$fieldId]);
            $newFieldValue->setFieldFacetValue($newFieldFacetValue);
            $this->om->persist($newFieldValue);
        }
    }

    public function getFieldValueByEntryAndField(Entry $entry, Field $field)
    {
        return $this->fieldValueRepo->findOneBy(['entry' => $entry, 'field' => $field]);
    }

    public function getFieldValuesByType(ClacoForm $clacoForm, $type)
    {
        return $this->fieldValueRepo->findFieldValuesByType($clacoForm, $type);
    }

    /**
     * @return Entry[]
     */
    public function getAllEntries(ClacoForm $clacoForm): array
    {
        return $this->entryRepo->findBy(['clacoForm' => $clacoForm]);
    }

    public function getPublishedEntriesByDates(ClacoForm $clacoForm, ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return $this->entryRepo->findPublishedEntriesByDates($clacoForm, $startDate, $endDate);
    }

    public function getPublishedEntriesByCategoriesAndDates(ClacoForm $clacoForm, array $categoriesIds = [], ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return $this->entryRepo->findPublishedEntriesByCategoriesAndDates($clacoForm, $categoriesIds, $startDate, $endDate);
    }

    public function hasRight(ClacoForm $clacoForm, $right): bool
    {
        return $this->authorization->isGranted($right, $clacoForm->getResourceNode());
    }

    public function isCategoryManager(ClacoForm $clacoForm, User $user): bool
    {
        $categories = $clacoForm->getCategories();

        foreach ($categories as $category) {
            $managers = $category->getManagers();

            foreach ($managers as $manager) {
                if ($manager->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isEntryManager(Entry $entry, User $user): bool
    {
        if ($this->hasRight($entry->getClacoForm(), 'EDIT')) {
            return true;
        }

        $categories = $entry->getCategories();
        foreach ($categories as $category) {
            $managers = $category->getManagers();

            foreach ($managers as $manager) {
                if ($manager->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function registerFile(ClacoForm $clacoForm, UploadedFile $file): array
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString();
        $dir = $this->filesDir.$ds.'clacoform'.$ds.$clacoForm->getUuid();
        $fileName = $hashName.'.'.$file->getClientOriginalExtension();

        $fileSize = $file->getSize(); // I can't get the filesize after move

        $file->move($dir, $fileName);

        return [
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientMimeType(),
            'mimeType' => $file->getClientMimeType(),
            'size' => $fileSize,
            'url' => '../files/clacoform'.$ds.$clacoForm->getUuid().$ds.$fileName,
        ];
    }
}
