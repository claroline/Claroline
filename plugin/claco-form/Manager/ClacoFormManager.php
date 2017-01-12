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

use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\ClacoFormWidgetConfig;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Event\Log\LogCategoryCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCategoryDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCategoryEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogClacoFormConfigureEvent;
use Claroline\ClacoFormBundle\Event\Log\LogClacoFormTemplateEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentStatusChangeEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryStatusChangeEvent;
use Claroline\ClacoFormBundle\Event\Log\LogFieldCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogFieldDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogFieldEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogKeywordCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogKeywordDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogKeywordEditEvent;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.claco_form_manager")
 */
class ClacoFormManager
{
    private $authorization;
    private $eventDispatcher;
    private $facetManager;
    private $messageManager;
    private $om;
    private $router;
    private $tokenStorage;
    private $translator;

    private $categoryRepo;
    private $commentRepo;
    private $entryRepo;
    private $fieldChoiceCategoryRepo;
    private $fieldRepo;
    private $fieldValueRepo;
    private $keywordRepo;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "facetManager"    = @DI\Inject("claroline.manager.facet_manager"),
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        FacetManager $facetManager,
        MessageManager $messageManager,
        ObjectManager $om,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->facetManager = $facetManager;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->categoryRepo = $om->getRepository('ClarolineClacoFormBundle:Category');
        $this->clacoFormRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoForm');
        $this->clacoFormWidgetConfigRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoFormWidgetConfig');
        $this->commentRepo = $om->getRepository('ClarolineClacoFormBundle:Comment');
        $this->entryRepo = $om->getRepository('ClarolineClacoFormBundle:Entry');
        $this->fieldChoiceCategoryRepo = $om->getRepository('ClarolineClacoFormBundle:FieldChoiceCategory');
        $this->fieldRepo = $om->getRepository('ClarolineClacoFormBundle:Field');
        $this->fieldValueRepo = $om->getRepository('ClarolineClacoFormBundle:FieldValue');
        $this->keywordRepo = $om->getRepository('ClarolineClacoFormBundle:Keyword');
    }

    public function initializeClacoForm(ClacoForm $clacoForm)
    {
        $clacoForm->setMaxEntries(0);
        $clacoForm->setCreationEnabled(true);
        $clacoForm->setEditionEnabled(true);
        $clacoForm->setModerated(false);

        $clacoForm->setDefaultHome('menu');
        $clacoForm->setDisplayNbEntries('none');
        $clacoForm->setMenuPosition('down');

        $clacoForm->setRandomEnabled(false);
        $clacoForm->setRandomCategories([]);
        $clacoForm->setRandomStartDate(null);
        $clacoForm->setRandomEndDate(null);

        $clacoForm->setSearchEnabled(true);
        $clacoForm->setSearchColumnEnabled(true);

        $clacoForm->setDisplayMetadata('none');

        $clacoForm->setDisplayCategories(false);
        $clacoForm->setOpenCategories(false);

        $clacoForm->setCommentsEnabled(false);
        $clacoForm->setAnonymousCommentsEnabled(false);
        $clacoForm->setModerateComments('none');
        $clacoForm->setDisplayComments(false);
        $clacoForm->setOpenComments(false);
        $clacoForm->setDisplayCommentAuthor(true);
        $clacoForm->setDisplayCommentDate(true);

        $clacoForm->setVotesEnabled(false);
        $clacoForm->setDisplayVotes(false);
        $clacoForm->setOpenVotes(false);
        $clacoForm->setVotesStartDate(null);
        $clacoForm->setVotesEndDate(null);

        $clacoForm->setKeywordsEnabled(false);
        $clacoForm->setNewKeywordsEnabled(false);
        $clacoForm->setDisplayKeywords(false);
        $clacoForm->setOpenKeywords(false);

        return $clacoForm;
    }

    public function persistClacoForm(ClacoForm $clacoForm)
    {
        $this->om->persist($clacoForm);
        $this->om->flush();
    }

    public function copyClacoForm(ClacoForm $clacoForm)
    {
        $newClacoForm = new ClacoForm();
        $newClacoForm->setName($clacoForm->getName());
        $newClacoForm->setTemplate($clacoForm->getTemplate());
        $this->om->persist($newClacoForm);

        return $newClacoForm;
    }

    public function saveClacoFormConfig(ClacoForm $clacoForm, array $configData)
    {
        $clacoForm->setDetails($configData);
        $randomStartDate = isset($configData['random_start_date']) ? new \DateTime($configData['random_start_date']) : null;
        $randomEndDate = isset($configData['random_end_date']) ? new \DateTime($configData['random_end_date']) : null;
        $votesStartDate = isset($configData['votes_start_date']) ? new \DateTime($configData['votes_start_date']) : null;
        $votesEndDate = isset($configData['votes_end_date']) ? new \DateTime($configData['votes_end_date']) : null;
        $clacoForm->setRandomStartDate($randomStartDate);
        $clacoForm->setRandomEndDate($randomEndDate);
        $clacoForm->setVotesStartDate($votesStartDate);
        $clacoForm->setVotesEndDate($votesEndDate);
        $this->persistClacoForm($clacoForm);
        $details = $clacoForm->getDetails();
        $event = new LogClacoFormConfigureEvent($clacoForm, $details);
        $this->eventDispatcher->dispatch('log', $event);

        return $details;
    }

    public function saveClacoFormTemplate(ClacoForm $clacoForm, $template)
    {
        $clacoFormTemplate = empty($template) ? null : $template;
        $clacoForm->setTemplate($clacoFormTemplate);
        $this->persistClacoForm($clacoForm);
        $event = new LogClacoFormTemplateEditEvent($clacoForm, $clacoFormTemplate);
        $this->eventDispatcher->dispatch('log', $event);

        return $clacoFormTemplate;
    }

    public function persistCategory(Category $category)
    {
        $this->om->persist($category);
        $this->om->flush();
    }

    public function createCategory(
        ClacoForm $clacoForm,
        $name,
        array $managers = [],
        $color = null,
        $notifyAddition = true,
        $notifyEdition = true,
        $notifyRemoval = true
    ) {
        $category = new Category();
        $category->setClacoForm($clacoForm);
        $category->setName($name);
        $category->setColor($color);
        $category->setNotifyAddition($notifyAddition);
        $category->setNotifyEdition($notifyEdition);
        $category->setNotifyRemoval($notifyRemoval);

        foreach ($managers as $manager) {
            $category->addManager($manager);
        }
        $this->persistCategory($category);
        $event = new LogCategoryCreateEvent($category);
        $this->eventDispatcher->dispatch('log', $event);

        return $category;
    }

    public function editCategory(
        Category $category,
        $name,
        array $managers = [],
        $color = null,
        $notifyAddition = true,
        $notifyEdition = true,
        $notifyRemoval = true
    ) {
        $category->setName($name);
        $category->setColor($color);
        $category->setNotifyAddition($notifyAddition);
        $category->setNotifyEdition($notifyEdition);
        $category->setNotifyRemoval($notifyRemoval);
        $category->emptyManagers();

        foreach ($managers as $manager) {
            $category->addManager($manager);
        }
        $this->persistCategory($category);
        $event = new LogCategoryEditEvent($category);
        $this->eventDispatcher->dispatch('log', $event);

        return $category;
    }

    public function deleteCategory(Category $category)
    {
        $clacoForm = $category->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $managers = $category->getManagers();
        $details = [];
        $details['id'] = $category->getId();
        $details['name'] = $category->getName();
        $details['details'] = $category->getDetails();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        $details['managers'] = [];

        foreach ($managers as $manager) {
            $details['managers'][] = [
                'id' => $manager->getId(),
                'username' => $manager->getUsername(),
                'firstName' => $manager->getFirstName(),
                'lastName' => $manager->getLastName(),
            ];
        }
        $this->om->remove($category);
        $this->om->flush();
        $event = new LogCategoryDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function deleteCategories(array $categories)
    {
        $this->om->startFlushSuite();

        foreach ($categories as $category) {
            $this->deleteCategory($category);
        }
        $this->om->endFlushSuite();
    }

    public function persistField(Field $field)
    {
        $this->om->persist($field);
        $this->om->flush();
    }

    public function createField(
        ClacoForm $clacoForm,
        $name,
        $type,
        $required = true,
        $isMetadata = false,
        array $choices = []
    ) {
        $this->om->startFlushSuite();
        $field = new Field();
        $field->setClacoForm($clacoForm);
        $field->setName($name);
        $field->setType($type);
        $field->setRequired($required);
        $field->setIsMetadata($isMetadata);
        $fieldFacet = $this->facetManager->createField($name, $required, $type, $clacoForm->getResourceNode());

        if ($this->facetManager->isTypeWithChoices($type)) {
            foreach ($choices as $choice) {
                $fieldFacetChoice = $this->facetManager->addFacetFieldChoice($choice['value'], $fieldFacet);

                if (!empty($choice['categoryId'])) {
                    $this->createFieldChoiceCategory($field, $choice['categoryId'], $choice['value'], $fieldFacetChoice);
                }
            }
        }
        $field->setFieldFacet($fieldFacet);
        $this->persistField($field);
        $this->om->endFlushSuite();
        $event = new LogFieldCreateEvent($field);
        $this->eventDispatcher->dispatch('log', $event);

        return $field;
    }

    public function editField(
        Field $field,
        $name,
        $type,
        $required = true,
        $isMetadata = false,
        array $oldChoices = [],
        array $newChoices = []
    ) {
        $this->om->startFlushSuite();
        $field->setName($name);
        $field->setType($type);
        $field->setRequired($required);
        $field->setIsMetadata($isMetadata);
        $fieldFacet = $field->getFieldFacet();
        $this->facetManager->editField($fieldFacet, $name, $required, $type);

        if ($this->facetManager->isTypeWithChoices($type)) {
            $this->updateChoices($field, $fieldFacet, $oldChoices);

            foreach ($newChoices as $choice) {
                $fieldFacetChoice = $this->facetManager->addFacetFieldChoice($choice['value'], $fieldFacet);

                if (!empty($choice['categoryId'])) {
                    $this->createFieldChoiceCategory($field, $choice['categoryId'], $choice['value'], $fieldFacetChoice);
                }
            }
        } else {
            $this->cleanChoices($fieldFacet);
        }
        $this->persistField($field);
        $this->om->endFlushSuite();
        $event = new LogFieldEditEvent($field);
        $this->eventDispatcher->dispatch('log', $event);

        return $field;
    }

    public function deleteField(Field $field)
    {
        $clacoForm = $field->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $details = [];
        $details['id'] = $field->getId();
        $details['type'] = $field->getType();
        $details['name'] = $field->getName();
        $details['required'] = $field->isRequired();
        $details['isMetadata'] = $field->getIsMetadata();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        $this->om->startFlushSuite();
        $fieldFacet = $field->getFieldFacet();

        if (!is_null($fieldFacet)) {
            $this->cleanChoices($fieldFacet);
            $this->om->remove($fieldFacet);
        }
        $this->om->remove($field);
        $this->om->endFlushSuite();
        $event = new LogFieldDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function updateChoices(Field $field, FieldFacet $fieldFacet, array $updatedChoices)
    {
        $choices = $fieldFacet->getFieldFacetChoicesArray();

        foreach ($choices as $choice) {
            $id = $choice->getId();
            $index = 0;
            $found = false;

            foreach ($updatedChoices as $updatedChoice) {
                if ($updatedChoice['index'] === $id) {
                    $found = true;
                    $choice->setLabel($updatedChoice['value']);
                    $this->om->persist($choice);
                    $choiceCategory = $this->getFieldChoiceCategoryByFieldAndChoice($field, $choice);

                    if (is_null($choiceCategory)) {
                        if (!empty($updatedChoice['categoryId'])) {
                            $this->createFieldChoiceCategory($field, $updatedChoice['categoryId'], $updatedChoice['value'], $choice);
                        }
                    } else {
                        $oldCategory = $choiceCategory->getCategory();

                        if ($oldCategory->getId() === $updatedChoice['categoryId']) {
                            $choiceCategory->setValue($updatedChoice['value']);
                            $this->om->persist($choiceCategory);
                        } else {
                            $this->om->remove($choiceCategory);

                            if (!empty($updatedChoice['categoryId'])) {
                                $this->createFieldChoiceCategory($field, $updatedChoice['categoryId'], $updatedChoice['value'], $choice);
                            }
                        }
                    }
                    break;
                }
                ++$index;
            }
            if (!$found) {
                $this->om->remove($choice);
            }
        }
        $this->om->persist($fieldFacet);
        $this->om->flush();
    }

    public function cleanChoices(FieldFacet $fieldFacet)
    {
        $choices = $fieldFacet->getFieldFacetChoices();

        foreach ($choices as $choice) {
            $this->om->remove($choice);
        }
        $this->om->flush();
    }

    public function persistFieldChoiceCategory(FieldChoiceCategory $fieldChoiceCategory)
    {
        $this->om->persist($fieldChoiceCategory);
        $this->om->flush();
    }

    public function createFieldChoiceCategory(Field $field, $categoryId, $value, FieldFacetChoice $fieldFacetChoice = null)
    {
        $fieldChoiceCategory = null;
        $category = $this->categoryRepo->findOneById($categoryId);

        if (!is_null($category)) {
            $fieldChoiceCategory = new FieldChoiceCategory();
            $fieldChoiceCategory->setField($field);
            $fieldChoiceCategory->setCategory($category);
            $fieldChoiceCategory->setValue($value);
            $fieldChoiceCategory->setFieldFacetChoice($fieldFacetChoice);
            $this->persistFieldChoiceCategory($fieldChoiceCategory);
        }

        return $fieldChoiceCategory;
    }

    public function persistKeyword(Keyword $keyword)
    {
        $this->om->persist($keyword);
        $this->om->flush();
    }

    public function createKeyword(ClacoForm $clacoForm, $name)
    {
        $keyword = $this->getKeywordByName($clacoForm, $name);

        if (is_null($keyword)) {
            $keyword = new Keyword();
            $keyword->setClacoForm($clacoForm);
            $keyword->setName($name);
            $this->persistKeyword($keyword);
            $event = new LogKeywordCreateEvent($keyword);
            $this->eventDispatcher->dispatch('log', $event);
        }

        return $keyword;
    }

    public function editKeyword(Keyword $keyword, $name)
    {
        $keyword->setName($name);
        $this->persistKeyword($keyword);
        $event = new LogKeywordEditEvent($keyword);
        $this->eventDispatcher->dispatch('log', $event);

        return $keyword;
    }

    public function deleteKeyword(Keyword $keyword)
    {
        $clacoForm = $keyword->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $details = [];
        $details['id'] = $keyword->getId();
        $details['name'] = $keyword->getName();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        $this->om->remove($keyword);
        $this->om->flush();
        $event = new LogKeywordDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function getEntriesForUser(ClacoForm $clacoForm, User $user = null)
    {
        $searchEnabled = $clacoForm->getSearchEnabled();
        $canEdit = $this->hasRight($clacoForm, 'EDIT');
        $entries = [];

        if ($canEdit) {
            $entries = $this->entryRepo->findBy(['clacoForm' => $clacoForm]);
        } elseif ($searchEnabled) {
            $entries = is_null($user) ? $this->getPublishedEntries($clacoForm) : $this->getPublishedAndManageableEntries($clacoForm, $user);
        }

        return $entries;
    }

    public function persistEntry(Entry $entry)
    {
        $this->om->persist($entry);
        $this->om->flush();
    }

    public function canCreateEntry(ClacoForm $clacoForm, User $user = null)
    {
        $maxEntries = $clacoForm->getMaxEntries();

        if (is_null($user)) {
            $canCreate = $clacoForm->isCreationEnabled() && ($maxEntries === 0);
        } else {
            $userEntries = $this->getEntriesByUser($clacoForm, $user);
            $canCreate = $clacoForm->isCreationEnabled() && (($maxEntries === 0) || ($maxEntries > count($userEntries)));
        }

        return $canCreate;
    }

    public function getRandomEntryId(ClacoForm $clacoForm)
    {
        $entryId = null;
        $entries = $this->getRandomEntries($clacoForm);
        $count = count($entries);

        if ($count > 0) {
            $randomIndex = rand(0, $count - 1);
            $entryId = $entries[$randomIndex]->getId();
        }

        return $entryId;
    }

    public function getRandomEntries(ClacoForm $clacoForm)
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

    public function createEntry(ClacoForm $clacoForm, array $entryData, $title, array $keywordsData = [], User $user = null)
    {
        $this->om->startFlushSuite();
        $now = new \DateTime();
        $status = $clacoForm->isModerated() ? Entry::PENDING : Entry::PUBLISHED;
        $entry = new Entry();
        $entry->setClacoForm($clacoForm);
        $entry->setUser($user);
        $entry->setTitle($title);
        $entry->setStatus($status);
        $entry->setCreationDate($now);
        $categories = [];

        if ($status === Entry::PUBLISHED) {
            $entry->setPublicationDate($now);
        }
        foreach ($entryData as $key => $value) {
            $field = $this->getFieldByClacoFormAndId($clacoForm, $key);

            if (!is_null($field)) {
                $fieldValue = $this->createFieldValue($entry, $field, $value, $user);
                $entry->addFieldValue($fieldValue);
                $type = $field->getType();

                if ($this->facetManager->isTypeWithChoices($type)) {
                    $categories = $this->getCategoriesFromFieldAndValue($field, $value);

                    foreach ($categories as $category) {
                        $entry->addCategory($category);
                    }
                }
            }
        }
        foreach ($keywordsData as $name) {
            if ($clacoForm->isNewKeywordsEnabled()) {
                $keyword = $this->createKeyword($clacoForm, $name);
            } else {
                $keyword = $this->getKeywordByName($clacoForm, $name);
            }
            if (!is_null($keyword)) {
                $entry->addKeyword($keyword);
            }
        }
        $this->persistEntry($entry);
        $event = new LogEntryCreateEvent($entry);
        $this->eventDispatcher->dispatch('log', $event);
        $this->om->endFlushSuite();
        $this->notifyCategoriesManagers($entry, [], $categories);

        return $entry;
    }

    public function editEntry(Entry $entry, array $entryData, $title, array $categoriesIds = [], array $keywordsData = [])
    {
        $this->om->startFlushSuite();
        $clacoForm = $entry->getClacoForm();
        $entry->setTitle($title);
        $oldCategories = $entry->getCategories();
        $entry->emptyCategories();
        $entry->emptyKeywords();
        $toRemove = [];
        $toAdd = [];
        $currentCategories = [];

        foreach ($categoriesIds as $categoryId) {
            $category = $this->categoryRepo->findOneById($categoryId);

            if (!is_null($category)) {
                $currentCategories[$category->getId()] = $category;
            }
        }
        foreach ($entryData as $key => $value) {
            $fieldValue = $this->getFieldValueByEntryAndFieldId($entry, $key);

            if (is_null($fieldValue)) {
                $field = $this->getFieldByClacoFormAndId($clacoForm, $key);

                if (!is_null($field)) {
                    $fieldValue = $this->createFieldValue($entry, $field, $value, $entry->getUser());
                    $entry->addFieldValue($fieldValue);
                    $type = $field->getType();

                    if ($this->facetManager->isTypeWithChoices($type)) {
                        $categoriesToAdd = $this->getCategoriesFromFieldAndValue($field, $value);

                        foreach ($categoriesToAdd as $catId => $cat) {
                            $toAdd[$catId] = $cat;
                        }
                    }
                }
            } else {
                $fieldFacetValue = $fieldValue->getFieldFacetValue();
                $field = $fieldValue->getField();
                $type = $field->getType();

                if ($this->facetManager->isTypeWithChoices($type)) {
                    $oldValue = $fieldFacetValue->getValue();
                    $categoriesToRemove = $this->getCategoriesFromFieldAndValue($field, $oldValue);
                    $categoriesToAdd = $this->getCategoriesFromFieldAndValue($field, $value);

                    foreach ($categoriesToRemove as $catId => $cat) {
                        $toRemove[$catId] = $cat;
                    }
                    foreach ($categoriesToAdd as $catId => $cat) {
                        $toAdd[$catId] = $cat;
                    }
                }
                $this->editFieldFacetValue($fieldFacetValue, $value);
            }
        }
        foreach ($toRemove as $categoryId => $category) {
            if (isset($currentCategories[$categoryId])) {
                unset($currentCategories[$categoryId]);
            }
        }
        foreach ($currentCategories as $category) {
            $entry->addCategory($category);
        }
        foreach ($toAdd as $category) {
            $entry->addCategory($category);
        }
        foreach ($keywordsData as $name) {
            if ($clacoForm->isNewKeywordsEnabled()) {
                $keyword = $this->createKeyword($clacoForm, $name);
            } else {
                $keyword = $this->getKeywordByName($clacoForm, $name);
            }
            if (!is_null($keyword)) {
                $entry->addKeyword($keyword);
            }
        }
        $entry->setEditionDate(new \DateTime());
        $this->persistEntry($entry);
        $event = new LogEntryEditEvent($entry);
        $this->eventDispatcher->dispatch('log', $event);
        $this->notifyCategoriesManagers($entry, $oldCategories, $entry->getCategories());
        $this->om->endFlushSuite();

        return $entry;
    }

    private function getCategoriesFromFieldAndValue(Field $field, $value)
    {
        $categories = [];
        $choiceCategories = [];
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $v) {
            $fccs = $this->getFieldChoicesCategoriesByFieldAndValue($field, $v);

            foreach ($fccs as $fcc) {
                $choiceCategories[] = $fcc;
            }
        }
        foreach ($choiceCategories as $choiceCategory) {
            $choiceValue = $choiceCategory->getValue();

            if (in_array($choiceValue, $values, true)) {
                $category = $choiceCategory->getCategory();
                $categories[$category->getId()] = $category;
            }
        }

        return $categories;
    }

    public function deleteEntry(Entry $entry)
    {
        $details = [];
        $details['id'] = $entry->getId();
        $details['title'] = $entry->getTitle();
        $details['status'] = $entry->getStatus();
        $details['creationDate'] = $entry->getCreationDate();
        $details['editionDate'] = $entry->getEditionDate();
        $details['publicationDate'] = $entry->getPublicationDate();
        $user = $entry->getUser();

        if (!is_null($user)) {
            $details['userId'] = $user->getId();
            $details['username'] = $user->getUsername();
            $details['firstName'] = $user->getFirstName();
            $details['lastName'] = $user->getLastName();
        }
        $fieldValues = $entry->getFieldValues();
        $details['values'] = [];

        foreach ($fieldValues as $fieldValue) {
            $fieldFacetValue = $fieldValue->getFieldFacetValue();
            $fieldFacet = $fieldFacetValue->getFieldFacet();
            $details['values'][] = [
                'id' => $fieldFacetValue->getId(),
                'value' => $fieldFacetValue->getValue(),
                'name' => $fieldFacet->getName(),
                'type' => $fieldFacet->getType(),
                'typeName' => $fieldFacet->getInputType(),
            ];
        }
        $categories = $entry->getCategories();
        $details['categories'] = [];

        foreach ($categories as $category) {
            $details['categories'][] = ['id' => $category->getId(), 'name' => $category->getName()];
        }
        $keywords = $entry->getKeywords();
        $details['keywords'] = [];

        foreach ($keywords as $keyword) {
            $details['keywords'][] = ['id' => $keyword->getId(), 'name' => $keyword->getName()];
        }
        $clacoForm = $entry->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();

        foreach ($fieldValues as $fieldValue) {
            $this->om->remove($fieldValue->getFieldFacetValue());
            $this->om->remove($fieldValue);
        }
        $this->notifyCategoriesManagers($entry, $categories);
        $this->om->remove($entry);
        $this->om->flush();
        $event = new LogEntryDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function changeEntryStatus(Entry $entry)
    {
        $status = $entry->getStatus();

        switch ($status) {
            case Entry::PENDING:
                $entry->setPublicationDate(new \DateTime());
            case Entry::UNPUBLISHED:
                $entry->setStatus(Entry::PUBLISHED);
                break;
            case Entry::PUBLISHED:
                $entry->setStatus(Entry::UNPUBLISHED);
                break;
        }
        $this->persistEntry($entry);
        $event = new LogEntryStatusChangeEvent($entry);
        $this->eventDispatcher->dispatch('log', $event);
        $categories = $entry->getCategories();
        $this->notifyCategoriesManagers($entry, $categories, $categories);

        return $entry;
    }

    public function notifyCategoriesManagers(Entry $entry, array $oldCategories = [], array $currentCategories = [])
    {
        $removedCategories = [];
        $editedCategories = [];
        $addedCategories = [];
        $clacoFormId = $entry->getClacoForm()->getId();
        $url = $this->router->generate('claro_claco_form_open', ['clacoForm' => $clacoFormId], true).'#/entries/'.$entry->getId().'/view';

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
                    $object = $this->translator->trans('entry_removal_from_category', ['%name%' => $category->getName()], 'clacoform');
                    $content = $this->translator->trans(
                        'entry_removal_from_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName()],
                        'clacoform'
                    );
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message, true, false);
                }
            }
        }
        foreach ($editedCategories as $category) {
            if ($category->getNotifyEdition()) {
                $managers = $category->getManagers();

                if (count($managers) > 0) {
                    $object = $this->translator->trans('entry_edition_in_category', ['%name%' => $category->getName()], 'clacoform');
                    $content = $this->translator->trans(
                        'entry_edition_in_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName(), '%url%' => $url],
                        'clacoform'
                    );
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message, true, false);
                }
            }
        }
        foreach ($addedCategories as $category) {
            if ($category->getNotifyAddition()) {
                $managers = $category->getManagers();

                if (count($managers) > 0) {
                    $object = $this->translator->trans('entry_addition_in_category', ['%name%' => $category->getName()], 'clacoform');
                    $content = $this->translator->trans(
                        'entry_addition_in_category_msg',
                        ['%title%' => $entry->getTitle(), '%category%' => $category->getName(), '%url%' => $url],
                        'clacoform'
                    );
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message, true, false);
                }
            }
        }
    }

    public function persistFieldValue(FieldValue $fieldValue)
    {
        $this->om->persist($fieldValue);
        $this->om->flush();
    }

    public function createFieldValue(Entry $entry, Field $field, $value, User $user = null)
    {
        $fieldFacet = $field->getFieldFacet();
        $fieldFacetValue = $this->createFieldFacetValue($fieldFacet, $value, $user);
        $fieldValue = new FieldValue();
        $fieldValue->setEntry($entry);
        $fieldValue->setField($field);
        $fieldValue->setFieldFacetValue($fieldFacetValue);
        $this->persistFieldValue($fieldValue);

        return  $fieldValue;
    }

    public function createFieldFacetValue(FieldFacet $fieldFacet, $value, User $user = null)
    {
        $fieldFacetValue = new FieldFacetValue();
        $fieldFacetValue->setUser($user);
        $fieldFacetValue->setFieldFacet($fieldFacet);

        switch ($fieldFacet->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ? new \DateTime($value) : $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
                $fieldFacetValue->setArrayValue($value);
                break;
            default:
                $fieldFacetValue->setStringValue($value);
        }
        $this->om->persist($fieldFacetValue);
        $this->om->flush();

        return $fieldFacetValue;
    }

    public function editFieldFacetValue(FieldFacetValue $fieldFacetValue, $value)
    {
        $fieldFacet = $fieldFacetValue->getFieldFacet();

        switch ($fieldFacet->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ? new \DateTime($value) : $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
                $fieldFacetValue->setArrayValue($value);
                break;
            default:
                $fieldFacetValue->setStringValue($value);
        }
        $this->om->persist($fieldFacetValue);
        $this->om->flush();

        return $fieldFacetValue;
    }

    public function persistComment(Comment $comment)
    {
        $this->om->persist($comment);
        $this->om->flush();
    }

    public function createComment(Entry $entry, $content, User $user = null)
    {
        $clacoForm = $entry->getClacoForm();
        $comment = new Comment();
        $comment->setEntry($entry);
        $comment->setUser($user);
        $comment->setContent($content);
        $comment->setCreationDate(new \DateTime());

        switch ($clacoForm->getModerateComments()) {
            case 'all':
                $status = Comment::PENDING;
                break;
            case 'anonymous':
                $status = is_null($user) ? Comment::PENDING : Comment::VALIDATED;
                break;
            default:
                $status = Comment::VALIDATED;
        }
        $comment->setStatus($status);
        $this->persistComment($comment);
        $event = new LogCommentCreateEvent($comment);
        $this->eventDispatcher->dispatch('log', $event);

        return $comment;
    }

    public function editComment(Comment $comment, $content)
    {
        $comment->setContent($content);
        $comment->setEditionDate(new \DateTime());
        $this->persistComment($comment);
        $event = new LogCommentEditEvent($comment);
        $this->eventDispatcher->dispatch('log', $event);

        return $comment;
    }

    public function changeCommentStatus(Comment $comment, $status)
    {
        $comment->setStatus($status);
        $this->persistComment($comment);
        $event = new LogCommentStatusChangeEvent($comment);
        $this->eventDispatcher->dispatch('log', $event);

        return $comment;
    }

    public function deleteComment(Comment $comment)
    {
        $details = [];
        $details['id'] = $comment->getId();
        $details['content'] = $comment->getContent();
        $details['status'] = $comment->getStatus();
        $details['creationDate'] = $comment->getCreationDate();
        $details['editionDate'] = $comment->getEditionDate();
        $user = $comment->getUser();

        if (!is_null($user)) {
            $details['userId'] = $user->getId();
            $details['username'] = $user->getUsername();
            $details['firstName'] = $user->getFirstName();
            $details['lastName'] = $user->getLastName();
        }
        $entry = $comment->getEntry();
        $details['entryId'] = $entry->getId();
        $details['entryTitle'] = $entry->getTitle();
        $clacoForm = $entry->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        $this->om->remove($comment);
        $this->om->flush();
        $event = new LogCommentDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function persistClacoFormWidgetConfiguration(ClacoFormWidgetConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }

    public function createClacoFormWidgetConfiguration(WidgetInstance $widgetInstance)
    {
        $config = new ClacoFormWidgetConfig();
        $config->setWidgetInstance($widgetInstance);
        $config->setNbEntries(1);
        $config->setShowFieldLabel(false);
        $this->persistClacoFormWidgetConfiguration($config);

        return $config;
    }

    public function getClacoFormWidgetConfiguration(WidgetInstance $widgetInstance)
    {
        $config = $this->clacoFormWidgetConfigRepo->findOneBy(['widgetInstance' => $widgetInstance->getId()]);

        if (is_null($config)) {
            $config = $this->createClacoFormWidgetConfiguration($widgetInstance);
        }

        return $config;
    }

    public function getNRandomEntries(ClacoForm $clacoForm, $nbEntries)
    {
        $randomEntries = $this->getRandomEntries($clacoForm);
        $count = count($randomEntries);

        if ($count > $nbEntries) {
            $entries = [];
            $indexes = [];

            for ($i = 0; $i < $nbEntries; ++$i) {
                $rand = rand(0, $count - 1);

                while (in_array($rand, $indexes)) {
                    $rand = ++$rand % $count;
                }
                $indexes[] = $rand;
            }
            foreach ($indexes as $index) {
                $entries[] = $randomEntries[$index];
            }
        } else {
            $entries = $randomEntries;
        }

        return $entries;
    }

    /*****************************************
     * Access to ClacoFormRepository methods *
     *****************************************/

    public function getClacoFormByResourceNode(ResourceNode $resourceNode)
    {
        return $this->clacoFormRepo->findOneBy(['resourceNode' => $resourceNode]);
    }

    /****************************************
     * Access to CategoryRepository methods *
     ****************************************/

    public function getCategoriesByClacoForm(ClacoForm $clacoForm)
    {
        return $this->categoryRepo->findBy(['clacoForm' => $clacoForm], ['name' => 'ASC']);
    }

    public function getCategoriesByManager(ClacoForm $clacoForm, User $manager)
    {
        return $this->categoryRepo->findCategoriesByManager($clacoForm, $manager);
    }

    /*************************************
     * Access to FieldRepository methods *
     *************************************/

    public function getFieldsByClacoForm(ClacoForm $clacoForm)
    {
        return $this->fieldRepo->findBy(['clacoForm' => $clacoForm], ['id' => 'ASC']);
    }

    public function getFieldByNameExcludingId(ClacoForm $clacoForm, $name, $id)
    {
        return $this->fieldRepo->findFieldByNameExcludingId($clacoForm, $name, $id);
    }

    public function getFieldByClacoFormAndId(ClacoForm $clacoForm, $id)
    {
        return $this->fieldRepo->findOneBy(['clacoForm' => $clacoForm, 'id' => $id]);
    }

    public function getNonConfidentialFieldsByResourceNode(ResourceNode $resourceNode)
    {
        return $this->fieldRepo->findNonConfidentialFieldsByResourceNode($resourceNode);
    }

    /******************************************
     * Access to FieldValueRepository methods *
     ******************************************/

    public function getFieldValueByEntryAndField(Entry $entry, Field $field)
    {
        return $this->fieldValueRepo->findOneBy(['entry' => $entry, 'field' => $field]);
    }

    public function getFieldValueByEntryAndFieldId(Entry $entry, $fieldId)
    {
        return $this->fieldValueRepo->findOneBy(['entry' => $entry, 'field' => $fieldId]);
    }

    /***************************************
     * Access to KeywordRepository methods *
     ***************************************/

    public function getKeywordByName(ClacoForm $clacoForm, $name)
    {
        return $this->keywordRepo->findKeywordByName($clacoForm, $name);
    }

    public function getKeywordByNameExcludingId(ClacoForm $clacoForm, $name, $id)
    {
        return $this->keywordRepo->findKeywordByNameExcludingId($clacoForm, $name, $id);
    }

    /*************************************
     * Access to EntryRepository methods *
     *************************************/

    public function getEntriesByUser(ClacoForm $clacoForm, User $user)
    {
        return $this->entryRepo->findBy(['clacoForm' => $clacoForm, 'user' => $user]);
    }

    public function getAllEntries(ClacoForm $clacoForm)
    {
        return $this->entryRepo->findBy(['clacoForm' => $clacoForm]);
    }

    public function getPublishedEntries(ClacoForm $clacoForm)
    {
        return $this->entryRepo->findPublishedEntries($clacoForm);
    }

    public function getManageableEntries(ClacoForm $clacoForm, User $user)
    {
        return $this->entryRepo->findManageableEntries($clacoForm, $user);
    }

    public function getPublishedAndManageableEntries(ClacoForm $clacoForm, User $user)
    {
        return $this->entryRepo->findPublishedAndManageableEntries($clacoForm, $user);
    }

    public function getEntriesByCategories(ClacoForm $clacoForm, array $categories)
    {
        return count($categories) > 0 ? $this->entryRepo->findEntriesByCategories($clacoForm, $categories) : [];
    }

    public function getPublishedEntriesByDates(ClacoForm $clacoForm, $startDate = null, $endDate = null)
    {
        return $this->entryRepo->findPublishedEntriesByDates($clacoForm, $startDate, $endDate);
    }

    public function getPublishedEntriesByCategoriesAndDates(ClacoForm $clacoForm, $categoriesIds = [], $startDate = null, $endDate = null)
    {
        return $this->entryRepo->findPublishedEntriesByCategoriesAndDates($clacoForm, $categoriesIds, $startDate, $endDate);
    }

    /***************************************
     * Access to CommentRepository methods *
     ***************************************/

    public function getCommentsByEntry(Entry $entry)
    {
        return $this->commentRepo->findBy(['entry' => $entry], ['creationDate' => 'DESC']);
    }

    public function getCommentsByEntryAndStatus(Entry $entry, $status)
    {
        return $this->commentRepo->findBy(['entry' => $entry, 'status' => $status], ['creationDate' => 'DESC']);
    }

    public function getAvailableCommentsForUser(Entry $entry, User $user)
    {
        return $this->commentRepo->findAvailableCommentsForUser($entry, $user);
    }

    /***************************************************
     * Access to FieldChoiceCategoryRepository methods *
     ***************************************************/

    public function getFieldChoicesCategoriesByField(Field $field)
    {
        return $this->fieldChoiceCategoryRepo->findBy(['field' => $field]);
    }

    public function getFieldChoiceCategoryByFieldAndChoice(Field $field, FieldFacetChoice $choice)
    {
        return $this->fieldChoiceCategoryRepo->findOneBy(['field' => $field, 'fieldFacetChoice' => $choice]);
    }

    public function getFieldChoicesCategoriesByFieldAndValue(Field $field, $value)
    {
        return $this->fieldChoiceCategoryRepo->findBy(['field' => $field, 'value' => $value]);
    }

    /******************
     * Rights methods *
     ******************/

    public function checkRight(ClacoForm $clacoForm, $right)
    {
        $collection = new ResourceCollection([$clacoForm->getResourceNode()]);

        if (!$this->authorization->isGranted($right, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    public function hasRight(ClacoForm $clacoForm, $right)
    {
        $collection = new ResourceCollection([$clacoForm->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }

    public function isEntryManager(Entry $entry, User $user)
    {
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

    public function hasEntryAccessRight(Entry $entry)
    {
        $clacoForm = $entry->getClacoForm();
        $user = $this->tokenStorage->getToken()->getUser();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || (
            $canOpen && (
               ($entry->getUser() === $user) ||
               (($user !== 'anon.') && $this->isEntryManager($entry, $user)) ||
               (($entry->getStatus() === Entry::PUBLISHED) && $clacoForm->getSearchEnabled())
            )
        );
    }

    public function hasEntryEditionRight(Entry $entry)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');
        $editionEnabled = $clacoForm->isEditionEnabled();

        return $canEdit || (
            $canOpen && (
                ($editionEnabled && ($entry->getUser() === $user)) ||
                (($user !== 'anon.') && $this->isEntryManager($entry, $user))
            )
        );
    }

    public function hasEntryModerationRight(Entry $entry)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || ($canOpen && ($user !== 'anon.') && $this->isEntryManager($entry, $user));
    }

    public function checkEntryAccess(Entry $entry)
    {
        if (!$this->hasEntryAccessRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkEntryEdition(Entry $entry)
    {
        if (!$this->hasEntryEditionRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkEntryModeration(Entry $entry)
    {
        if (!$this->hasEntryModerationRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkCommentCreationRight(Entry $entry)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();

        if (!$this->hasEntryAccessRight($entry) ||
            !$clacoForm->isCommentsEnabled() ||
            (($user === 'anon.') && !$clacoForm->isAnonymousCommentsEnabled())) {
            throw new AccessDeniedException();
        }
    }

    public function checkCommentEditionRight(Comment $comment)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $entry = $comment->getEntry();

        if (!$this->hasEntryAccessRight($entry) || (($user !== $comment->getUser()) && !$this->hasEntryModerationRight($entry))) {
            throw new AccessDeniedException();
        }
    }
}
