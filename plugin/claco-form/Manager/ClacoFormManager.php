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
use Claroline\ClacoFormBundle\Entity\EntryUser;
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
use Claroline\ClacoFormBundle\Event\Log\LogEntryUserChangeEvent;
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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\MessageBundle\Manager\MessageManager;
use Claroline\PdfGeneratorBundle\Manager\PdfManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    private $archiveDir;
    private $authorization;
    private $configHandler;
    private $eventDispatcher;
    private $facetManager;
    private $fileSystem;
    private $filesDir;
    private $locationManager;
    private $messageManager;
    private $om;
    private $pdfManager;
    private $router;
    private $templating;
    private $tokenStorage;
    private $translator;
    private $userManager;
    private $utils;

    private $categoryRepo;
    private $commentRepo;
    private $entryRepo;
    private $entryUserRepo;
    private $fieldChoiceCategoryRepo;
    private $fieldRepo;
    private $fieldValueRepo;
    private $keywordRepo;

    /**
     * @DI\InjectParams({
     *     "archiveDir"      = @DI\Inject("%claroline.param.platform_generated_archive_path%"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "configHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "facetManager"    = @DI\Inject("claroline.manager.facet_manager"),
     *     "fileSystem"      = @DI\Inject("filesystem"),
     *     "filesDir"        = @DI\Inject("%claroline.param.files_directory%"),
     *     "locationManager" = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pdfManager"      = @DI\Inject("claroline.manager.pdf_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "templating"      = @DI\Inject("templating"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
     *     "utils"           = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        $archiveDir,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $configHandler,
        EventDispatcherInterface $eventDispatcher,
        FacetManager $facetManager,
        Filesystem $fileSystem,
        $filesDir,
        LocationManager $locationManager,
        MessageManager $messageManager,
        ObjectManager $om,
        PdfManager $pdfManager,
        RouterInterface $router,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        UserManager $userManager,
        ClaroUtilities $utils
    ) {
        $this->archiveDir = $archiveDir;
        $this->authorization = $authorization;
        $this->configHandler = $configHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->facetManager = $facetManager;
        $this->fileSystem = $fileSystem;
        $this->filesDir = $filesDir;
        $this->locationManager = $locationManager;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->pdfManager = $pdfManager;
        $this->router = $router;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->utils = $utils;
        $this->categoryRepo = $om->getRepository('ClarolineClacoFormBundle:Category');
        $this->clacoFormRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoForm');
        $this->clacoFormWidgetConfigRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoFormWidgetConfig');
        $this->commentRepo = $om->getRepository('ClarolineClacoFormBundle:Comment');
        $this->entryRepo = $om->getRepository('ClarolineClacoFormBundle:Entry');
        $this->entryUserRepo = $om->getRepository('ClarolineClacoFormBundle:EntryUser');
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
        $clacoForm->setSearchColumns([
            'title',
            'creationDateString',
            'userString',
            'categoriesString',
            'keywordsString',
        ]);

        $clacoForm->setDisplayMetadata('none');

        $clacoForm->setLockedFieldsFor('user');

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

        $clacoForm->setUseTemplate(false);
        $clacoForm->setDefaultDisplayMode('table');
        $clacoForm->setDisplayTitle('title');
        $clacoForm->setDisplaySubtitle('title');
        $clacoForm->setDisplayContent('title');

        return $clacoForm;
    }

    public function persistClacoForm(ClacoForm $clacoForm)
    {
        $this->om->persist($clacoForm);
        $this->om->flush();
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

    public function saveClacoFormTemplate(ClacoForm $clacoForm, $template, $useTemplate)
    {
        $clacoFormTemplate = empty($template) ? null : $template;
        $clacoFormUseTemplate = $clacoFormTemplate ? $useTemplate : false;
        $clacoForm->setTemplate($clacoFormTemplate);
        $clacoForm->setUseTemplate($clacoFormUseTemplate);
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
        $notifyRemoval = true,
        $notifyPendingComment = true
    ) {
        $category = new Category();
        $category->setClacoForm($clacoForm);
        $category->setName($name);
        $category->setColor($color);
        $category->setNotifyAddition($notifyAddition);
        $category->setNotifyEdition($notifyEdition);
        $category->setNotifyRemoval($notifyRemoval);
        $category->setNotifyPendingComment($notifyPendingComment);

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
        $notifyRemoval = true,
        $notifyPendingComment = true
    ) {
        $category->setName($name);
        $category->setColor($color);
        $category->setNotifyAddition($notifyAddition);
        $category->setNotifyEdition($notifyEdition);
        $category->setNotifyRemoval($notifyRemoval);
        $category->setNotifyPendingComment($notifyPendingComment);
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
        $locked = false,
        $lockedEditionOnly = false,
        $hidden = false,
        array $choices = [],
        array $choicesChildren = [],
        array $details = []
    ) {
        $this->om->startFlushSuite();
        $field = new Field();
        $field->setClacoForm($clacoForm);
        $field->setName($name);
        $field->setType($type);
        $field->setRequired($required);
        $field->setIsMetadata($isMetadata);
        $field->setLocked($locked);
        $field->setLockedEditionOnly($lockedEditionOnly);
        $field->setHidden($hidden);
        $field->setDetails($details);
        $facetType = $type === FieldFacet::SELECT_TYPE && count($choicesChildren) > 0 ?
            FieldFacet::CASCADE_SELECT_TYPE :
            $type;
        $fieldFacet = $this->facetManager->createField($name, $required, $facetType, $clacoForm->getResourceNode());

        if ($this->facetManager->isTypeWithChoices($type)) {
            foreach ($choices as $choice) {
                $fieldFacetChoice = $this->facetManager->addFacetFieldChoice($choice['value'], $fieldFacet, null, $choice['index']);

                if (!empty($choice['categoryId'])) {
                    $this->createFieldChoiceCategory($field, $choice['categoryId'], $choice['value'], $fieldFacetChoice);
                }
                $this->createChildrenChoices($field, $fieldFacetChoice, $choice['index'], $choicesChildren);
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
        $locked = false,
        $lockedEditionOnly = false,
        $hidden = false,
        array $choices = [],
        array $choicesChildren = [],
        array $details = []
    ) {
        $oldChoices = [];

        foreach ($choices as $choice) {
            if (!$choice['new']) {
                $oldChoices[] = $choice;
            }
        }
        foreach ($choicesChildren as $choicesList) {
            foreach ($choicesList as $choice) {
                if (!$choice['new']) {
                    $oldChoices[] = $choice;
                }
            }
        }
        $this->om->startFlushSuite();
        $field->setName($name);
        $field->setType($type);
        $field->setRequired($required);
        $field->setIsMetadata($isMetadata);
        $field->setLocked($locked);
        $field->setLockedEditionOnly($lockedEditionOnly);
        $field->setHidden($hidden);
        $field->setDetails($details);
        $fieldFacet = $field->getFieldFacet();
        $facetType = $type === FieldFacet::SELECT_TYPE && count($choicesChildren) > 0 ?
            FieldFacet::CASCADE_SELECT_TYPE :
            $type;
        $this->facetManager->editField($fieldFacet, $name, $required, $facetType);

        if ($this->facetManager->isTypeWithChoices($type)) {
            $this->updateChoices($field, $fieldFacet, $oldChoices);

            foreach ($choices as $choice) {
                if ($choice['new']) {
                    $fieldFacetChoice = $this->facetManager->addFacetFieldChoice($choice['value'], $fieldFacet, null, $choice['index']);

                    if (!empty($choice['categoryId'])) {
                        $this->createFieldChoiceCategory($field, $choice['categoryId'], $choice['value'], $fieldFacetChoice);
                    }
                } else {
                    $fieldFacetChoice = $this->facetManager->getFieldFacetChoiceById($choice['index']);
                }
                if (!empty($fieldFacetChoice)) {
                    $this->createChildrenChoices($field, $fieldFacetChoice, $choice['index'], $choicesChildren);
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
                $fieldFacet->removeFieldChoice($choice);
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

    private function createChildrenChoices(Field $field, FieldFacetChoice $parent, $index, $choicesChildren)
    {
        if (isset($choicesChildren[$index])) {
            foreach ($choicesChildren[$index] as $childChoice) {
                if ($childChoice['new']) {
                    $child = $this->facetManager->addFacetFieldChoice(
                        $childChoice['value'],
                        $parent->getFieldFacet(),
                        $parent,
                        $childChoice['index']
                    );

                    if (!empty($childChoice['categoryId'])) {
                        $this->createFieldChoiceCategory($field, $childChoice['categoryId'], $childChoice['value'], $child);
                    }
                } else {
                    $child = $this->facetManager->getFieldFacetChoiceById($childChoice['index']);
                }
                $this->createChildrenChoices($field, $child, $childChoice['index'], $choicesChildren);
            }
        }
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
        if ($this->hasRight($clacoForm, 'EDIT')) {
            $canCreate = true;
        } else {
            $maxEntries = $clacoForm->getMaxEntries();

            if (is_null($user)) {
                $canCreate = $clacoForm->isCreationEnabled() && ($maxEntries === 0);
            } else {
                $userEntries = $this->getEntriesByUser($clacoForm, $user);
                $canCreate = $clacoForm->isCreationEnabled() && (($maxEntries === 0) || ($maxEntries > count($userEntries)));
            }
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

    public function getRandomEntriesByCategories(ClacoForm $clacoForm, array $categoriesIds)
    {
        return count($categoriesIds) > 0 ?
            $this->getPublishedEntriesByCategoriesAndDates($clacoForm, $categoriesIds) :
            $this->getPublishedEntriesByDates($clacoForm);
    }

    public function createEntry(
        ClacoForm $clacoForm,
        array $entryData,
        $title,
        array $keywordsData = [],
        User $user = null,
        array $files = []
    ) {
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

            if (!is_null($field) && $value !== '') {
                $type = $field->getType();

                if ($this->facetManager->isFileType($type)) {
                    $values = [];

                    foreach ($this->filterFieldFiles($field->getId(), $files) as $file) {
                        $values[] = $this->registerFile($clacoForm, $file);
                    }
                    $value = $values;
                }
                $fieldValue = $this->createFieldValue($entry, $field, $value, $user);
                $entry->addFieldValue($fieldValue);

                if ($this->facetManager->isTypeWithChoices($type)) {
                    $choiceCategories = $this->getCategoriesFromFieldAndValue($field, $value);

                    foreach ($choiceCategories as $category) {
                        $entry->addCategory($category);
                        $categories[$category->getId()] = $category;
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

        if (!is_null($user)) {
            $this->createEntryUser($entry, $user, false, true, true, true);
        }
        $event = new LogEntryCreateEvent($entry);
        $this->eventDispatcher->dispatch('log', $event);
        $this->om->endFlushSuite();
        $this->notifyCategoriesManagers($entry, [], $categories);

        return $entry;
    }

    public function editEntry(
        Entry $entry,
        array $entryData,
        $title,
        array $categoriesIds = [],
        array $keywordsData = [],
        array $files = []
    ) {
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
                if ($value !== '') {
                    $field = $this->getFieldByClacoFormAndId($clacoForm, $key);

                    if (!is_null($field)) {
                        $type = $field->getType();

                        if ($this->facetManager->isFileType($type)) {
                            $values = [];

                            foreach ($this->filterFieldFiles($field->getId(), $files) as $file) {
                                $values[] = $this->registerFile($clacoForm, $file);
                            }
                            $value = $values;
                        }
                        $fieldValue = $this->createFieldValue($entry, $field, $value, $entry->getUser());
                        $entry->addFieldValue($fieldValue);

                        if ($this->facetManager->isTypeWithChoices($type)) {
                            $categoriesToAdd = $this->getCategoriesFromFieldAndValue($field, $value);

                            foreach ($categoriesToAdd as $catId => $cat) {
                                $toAdd[$catId] = $cat;
                            }
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
                if ($this->facetManager->isFileType($type)) {
                    $values = [];

                    foreach ($value as $v) {
                        if (isset($v['url'])) {
                            $values[] = $v;
                        }
                    }
                    $this->removeOldFiles($fieldFacetValue->getValue(), $values);
                    foreach ($this->filterFieldFiles($field->getId(), $files) as $file) {
                        $values[] = $this->registerFile($clacoForm, $file);
                    }
                    $value = $values;
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
        $this->notifyUsers($entry, 'edition');
        $this->om->endFlushSuite();

        return $entry;
    }

    private function getCategoriesFromFieldAndValue(Field $field, $value)
    {
        $fieldFacet = $field->getFieldFacet();
        $categories = [];
        $choiceCategories = [];
        $values = is_array($value) ? $value : [$value];

        if ($fieldFacet->getType() === FieldFacet::CASCADE_SELECT_TYPE) {
            $choice = null;

            foreach ($values as $val) {
                $parent = $choice;
                $choice = $this->facetManager->getChoiceByFieldFacetAndValueAndParent($fieldFacet, $val, $parent);

                if ($choice) {
                    $fcc = $this->getFieldChoiceCategoryByFieldAndChoice($field, $choice);

                    if (!empty($fcc)) {
                        $choiceCategories[] = $fcc;
                    }
                }
            }
        } else {
            foreach ($values as $v) {
                $fccs = $this->getFieldChoicesCategoriesByFieldAndValue($field, $v);

                foreach ($fccs as $fcc) {
                    $choiceCategories[] = $fcc;
                }
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
        $this->notifyUsers($entry, 'deletion');
        $entryUsers = $entry->getEntryUsers();

        foreach ($entryUsers as $entryUser) {
            $this->om->remove($entryUser);
        }
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

    public function changeEntryOwner(Entry $entry, User $user)
    {
        $entry->setUser($user);
        $this->persistEntry($entry);
        $event = new LogEntryUserChangeEvent($entry);
        $this->eventDispatcher->dispatch('log', $event);

        return $entry;
    }

    public function notifyCategoriesManagers(Entry $entry, array $oldCategories = [], array $currentCategories = [])
    {
        $removedCategories = [];
        $editedCategories = [];
        $addedCategories = [];
        $clacoFormName = $entry->getClacoForm()->getResourceNode()->getName();
        $clacoFormId = $entry->getClacoForm()->getId();
        $url = $this->router->generate('claro_claco_form_open', ['clacoForm' => $clacoFormId], true).
            '#/entries/'.$entry->getId().'/view';

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
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message);
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
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message);
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
                    $message = $this->messageManager->create($content, $object, $managers);
                    $this->messageManager->send($message);
                }
            }
        }
    }

    public function notifyPendingComment(Entry $entry, Comment $comment)
    {
        $clacoForm = $entry->getClacoForm();

        if ($clacoForm->getDisplayComments()) {
            $url = $this->router->generate('claro_claco_form_open', ['clacoForm' => $clacoForm->getId()], true).
                '#/entries/'.$entry->getId().'/view';
            $receivers = [];
            $categories = $entry->getCategories();

            foreach ($categories as $category) {
                if ($category->getNotifyPendingComment()) {
                    $managers = $category->getManagers();

                    foreach ($managers as $manager) {
                        $receivers[$manager->getId()] = $manager;
                    }
                }
            }
            if (count($receivers) > 0) {
                $object = '['.
                    $this->translator->trans('entry_pending_comment', [], 'clacoform').
                    '] '.
                    $entry->getTitle().
                    ' - '.
                    $clacoForm->getResourceNode()->getName();
                $content = $comment->getContent().
                    '<br><br>'.
                    $this->translator->trans('link_to_entry', [], 'clacoform').
                    ' : <a href="'.$url.'">'.
                    $this->translator->trans('here', [], 'platform').
                    '</a><br><br>';

                $message = $this->messageManager->create($content, $object, $receivers);
                $this->messageManager->send($message);
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
                $date = $value ? new \DateTime($value) : null;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
            case FieldFacet::CASCADE_SELECT_TYPE:
                $fieldFacetValue->setArrayValue(is_array($value) ? $value : [$value]);
                break;
            case FieldFacet::FILE_TYPE:
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
                if (is_array($value)) {
                    $date = new \DateTime($value['date']);
                } else {
                    $date = is_string($value) ? new \DateTime($value) : $value;
                }
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $floatValue = $value === '' ? null : $value;
                $fieldFacetValue->setFloatValue($floatValue);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
            case FieldFacet::CASCADE_SELECT_TYPE:
                $fieldFacetValue->setArrayValue(is_array($value) ? $value : [$value]);
                break;
            case FieldFacet::FILE_TYPE:
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

        if ($comment->getStatus() === Comment::VALIDATED) {
            $this->notifyUsers($entry, 'comment', $content);
        } else {
            $this->notifyPendingComment($entry, $comment);
        }

        return $comment;
    }

    public function editComment(Comment $comment, $content)
    {
        $comment->setContent($content);
        $comment->setEditionDate(new \DateTime());
        $this->persistComment($comment);
        $event = new LogCommentEditEvent($comment);
        $this->eventDispatcher->dispatch('log', $event);

        if ($comment->getStatus() === Comment::VALIDATED) {
            $this->notifyUsers($comment->getEntry(), 'comment', $content);
        }

        return $comment;
    }

    public function changeCommentStatus(Comment $comment, $status)
    {
        $comment->setStatus($status);
        $this->persistComment($comment);
        $event = new LogCommentStatusChangeEvent($comment);
        $this->eventDispatcher->dispatch('log', $event);

        if ($comment->getStatus() === Comment::VALIDATED) {
            $this->notifyUsers($comment->getEntry(), 'comment', $comment->getContent());
        }

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

    public function getNRandomEntries(ClacoForm $clacoForm, $nbEntries, array $categoriesIds)
    {
        $randomEntries = $this->getRandomEntriesByCategories($clacoForm, $categoriesIds);
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

    public function createEntryUser(
        Entry $entry,
        User $user,
        $shared = false,
        $notifyEdition = false,
        $notifyComment = false,
        $notifyVote = false
    ) {
        $entryUser = new EntryUser();
        $entryUser->setEntry($entry);
        $entryUser->setUser($user);
        $entryUser->setShared($shared);
        $entryUser->setNotifyEdition($notifyEdition);
        $entryUser->setNotifyComment($notifyComment);
        $entryUser->setNotifyVote($notifyVote);
        $this->om->persist($entryUser);
        $this->om->flush();

        return $entryUser;
    }

    public function getEntryUser(Entry $entry, User $user)
    {
        $entryUser = $this->entryUserRepo->findOneBy(['entry' => $entry, 'user' => $user]);

        if (empty($entryUser)) {
            $entryUser = $this->createEntryUser($entry, $user);
        }

        return $entryUser;
    }

    public function persistEntryUser(EntryUser $entryUser)
    {
        $this->om->persist($entryUser);
        $this->om->flush();
    }

    public function notifyUsers(Entry $entry, $type, $data = null)
    {
        $sendMessage = false;
        $receivers = [];
        $clacoForm = $entry->getClacoForm();
        $url = $this->router->generate('claro_claco_form_open', ['clacoForm' => $clacoForm->getId()], true).
            '#/entries/'.$entry->getId().'/view';

        switch ($type) {
            case 'edition':
                $sendMessage = true;
                $entryUsers = $this->entryUserRepo->findBy(['entry' => $entry, 'notifyEdition' => true]);

                foreach ($entryUsers as $entryUser) {
                    $receivers[] = $entryUser->getUser();
                }
                if ($sendMessage && count($receivers) > 0) {
                    $subject = '['.
                        $this->translator->trans('entry_edition', [], 'clacoform').
                        '] '.
                        $entry->getTitle().
                        ' - '.
                        $clacoForm->getResourceNode()->getName();
                    $content = $this->translator->trans('link_to_entry', [], 'clacoform').
                        ' : <a href="'.$url.'">'.
                        $this->translator->trans('here', [], 'platform').
                        '</a><br><br>';
                }
                break;
            case 'deletion':
                $sendMessage = true;
                $entryUsers = $this->entryUserRepo->findBy(['entry' => $entry, 'notifyEdition' => true]);

                foreach ($entryUsers as $entryUser) {
                    $receivers[] = $entryUser->getUser();
                }
                if ($sendMessage && count($receivers) > 0) {
                    $subject = '['.
                        $this->translator->trans('entry_deletion', [], 'clacoform').
                        '] '.
                        $entry->getTitle().
                        ' - '.
                        $clacoForm->getResourceNode()->getName();
                    $content = $this->translator->trans('entry_deletion_msg', ['%title%' => $entry->getTitle()], 'clacoform');
                }
                break;
            case 'comment':
                $sendMessage = $clacoForm->getDisplayComments();

                if ($sendMessage) {
                    $entryUsers = $this->entryUserRepo->findBy(['entry' => $entry, 'notifyComment' => true]);

                    foreach ($entryUsers as $entryUser) {
                        $receivers[] = $entryUser->getUser();
                    }
                    if (count($receivers) > 0) {
                        $subject = '['.
                            $this->translator->trans('entry_comment', [], 'clacoform').
                            '] '.
                            $entry->getTitle().
                            ' - '.
                            $clacoForm->getResourceNode()->getName();
                        $content = $data.
                            '<br><br>'.
                            $this->translator->trans('link_to_entry', [], 'clacoform').
                            ' : <a href="'.$url.'">'.
                            $this->translator->trans('here', [], 'platform').
                            '</a><br><br>';
                    }
                }
                break;
        }
        if ($sendMessage && count($receivers) > 0) {
            $message = $this->messageManager->create($content, $subject, $receivers);
            $this->messageManager->send($message);
        }
    }

    public function hasFiles(ClacoForm $clacoForm)
    {
        $hasFiles = false;
        $fields = $clacoForm->getFields();

        foreach ($fields as $field) {
            if ($field->getType() === FieldFacet::FILE_TYPE) {
                $hasFiles = true;
                break;
            }
        }

        return $hasFiles;
    }

    public function exportEntries(ClacoForm $clacoForm)
    {
        $entriesData = [];
        $fields = $clacoForm->getFields();
        $entries = $this->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $user = $entry->getUser();
            $publicationDate = $entry->getPublicationDate();
            $editionDate = $entry->getEditionDate();
            $fieldValues = $entry->getFieldValues();
            $data = [];
            $data['id'] = $entry->getId();
            $data['title'] = $entry->getTitle();
            $data['author'] = empty($user) ?
                $this->translator->trans('anonymous', [], 'platform') :
                $user->getFirstName().' '.$user->getLastName();
            $data['publicationDate'] = empty($publicationDate) ? '' : $publicationDate->format('d/m/Y');
            $data['editionDate'] = empty($editionDate) ? '' : $editionDate->format('d/m/Y');

            foreach ($fieldValues as $fiedValue) {
                $field = $fiedValue->getField();
                $fieldFacet = $field->getFieldFacet();
                $fieldFacetValue = $fiedValue->getFieldFacetValue();
                $val = $fieldFacetValue->getValue();

                switch ($fieldFacet->getType()) {
                    case FieldFacet::DATE_TYPE:
                        $value = !empty($val) ? $val->format('d/m/Y') : '';
                        break;
                    case FieldFacet::CHECKBOXES_TYPE:
                    case FieldFacet::CASCADE_SELECT_TYPE:
                        $value = is_array($val) ? implode(', ', $val) : '';
                        break;
                    case FieldFacet::COUNTRY_TYPE:
                        $value = $this->locationManager->getCountryByCode($val);
                        break;
                    case FieldFacet::FILE_TYPE:
                        $values = [];

                        foreach ($val as $fileValue) {
                            $values[] = '['.implode(', ', $fileValue).']';
                        }
                        $value = implode(', ', $values);
                        break;
                    default:
                        $value = $val;

                }
                $data[$field->getId()] = $value;
            }
            $entriesData[] = $data;
        }

        return $this->templating->render(
            'ClarolineClacoFormBundle:ClacoForm:entries_export.html.twig',
            [
                'fields' => $fields,
                'entries' => $entriesData,
            ]
        );
    }

    public function zipEntries($content, ClacoForm $clacoForm)
    {
        $archive = new \ZipArchive();
        $pathArch = $this->configHandler->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$this->utils->generateGuid().'.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $archive->addFromString($clacoForm->getResourceNode()->getName().'.xls', $content);

        $entries = $this->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $fieldValues = $entry->getFieldValues();

            foreach ($fieldValues as $fiedValue) {
                $field = $fiedValue->getField();
                $fieldFacetValue = $fiedValue->getFieldFacetValue();

                if ($field->getType() === FieldFacet::FILE_TYPE) {
                    $files = $fieldFacetValue->getValue();
                    foreach ($files as $file) {
                        $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$file['url'];
                        $fileParts = explode('/', $file['url']);
                        $fileName = count($fileParts) > 0 ? $fileParts[count($fileParts) - 1] : $file['name'];
                        $archive->addFile(
                            $filePath,
                            'files'.DIRECTORY_SEPARATOR.$entry->getId().DIRECTORY_SEPARATOR.$fileName
                        );
                    }
                }
            }
        }
        $archive->close();
        file_put_contents($this->archiveDir, $pathArch."\n", FILE_APPEND);

        return $pathArch;
    }

    public function generatePdfForEntry(Entry $entry, User $user)
    {
        $clacoForm = $entry->getClacoForm();
        $fields = $clacoForm->getFields();
        $fieldValues = [];

        foreach ($entry->getFieldValues() as $fieldValue) {
            $field = $fieldValue->getField();
            $fieldValues[$field->getId()] = $fieldValue->getFieldFacetValue()->getValue();
        }
        $canEdit = $this->hasRight($clacoForm, 'EDIT');
        $template = $clacoForm->getTemplate();
        $useTemplate = $clacoForm->getUseTemplate();
        $displayMeta = $clacoForm->getDisplayMetadata();
        $isEntryManager = $user !== 'anon.' && $this->isEntryManager($entry, $user);
        $withMeta = $canEdit || $displayMeta === 'all' || ($displayMeta === 'manager' && $isEntryManager);
        $countries = $this->locationManager->getCountries();

        if (!empty($template) && $useTemplate) {
            $template = str_replace('%clacoform_entry_title%', $entry->getTitle(), $template);

            foreach ($fields as $field) {
                if (!$field->isHidden() && ($withMeta || !$field->getIsMetadata()) && isset($fieldValues[$field->getId()])) {
                    $fieldFacet = $field->getFieldFacet();

                    switch ($fieldFacet->getType()) {
                        case FieldFacet::DATE_TYPE:
                            $value = $fieldValues[$field->getId()]->format('d/m/Y');
                            break;
                        case FieldFacet::CHECKBOXES_TYPE:
                        case FieldFacet::CASCADE_SELECT_TYPE:
                            $value = implode(', ', $fieldValues[$field->getId()]);
                            break;
                        case FieldFacet::COUNTRY_TYPE:
                            $value = $this->locationManager->getCountryByCode($fieldValues[$field->getId()]);
                            break;
                        case FieldFacet::FILE_TYPE:
                            $values = [];

                            foreach ($fieldValues[$field->getId()] as $fileValue) {
                                $values[] = '['.implode(', ', $fileValue).']';
                            }
                            $value = implode(', ', $values);
                            break;
                        default:
                            $value = $fieldValues[$field->getId()];
                    }
                } else {
                    $value = '';
                }
                $name = 'field_'.$field->getId();
                $template = str_replace("%$name%", $value, $template);
            }
        }
        $html = $this->templating->render(
            'ClarolineClacoFormBundle:ClacoForm:entry.html.twig',
            [
                'entry' => $entry,
                'template' => $template,
                'useTemplate' => $useTemplate,
                'withMeta' => $withMeta,
                'fields' => $fields,
                'fieldValues' => $fieldValues,
                'countries' => $countries,
            ]
        );

        return $this->pdfManager->create($html, $entry->getTitle(), $user, 'clacoform_entries');
    }

    public function removeQuote($str)
    {
        return str_replace('\'', ' ', $str);
    }

    public function removeAccent($str)
    {
        $convertedStr = $str;
        $convertedStr = str_replace('Ç', 'C', $convertedStr);
        $convertedStr = str_replace('ç', 'c', $convertedStr);
        $convertedStr = str_replace('è', 'e', $convertedStr);
        $convertedStr = str_replace('é', 'e', $convertedStr);
        $convertedStr = str_replace('ê', 'e', $convertedStr);
        $convertedStr = str_replace('ë', 'e', $convertedStr);
        $convertedStr = str_replace('È', 'E', $convertedStr);
        $convertedStr = str_replace('É', 'E', $convertedStr);
        $convertedStr = str_replace('Ê', 'E', $convertedStr);
        $convertedStr = str_replace('Ë', 'E', $convertedStr);
        $convertedStr = str_replace('à', 'a', $convertedStr);
        $convertedStr = str_replace('á', 'a', $convertedStr);
        $convertedStr = str_replace('â', 'a', $convertedStr);
        $convertedStr = str_replace('ã', 'a', $convertedStr);
        $convertedStr = str_replace('ä', 'a', $convertedStr);
        $convertedStr = str_replace('ä', 'a', $convertedStr);
        $convertedStr = str_replace('@', 'A', $convertedStr);
        $convertedStr = str_replace('À', 'A', $convertedStr);
        $convertedStr = str_replace('Á', 'A', $convertedStr);
        $convertedStr = str_replace('Â', 'A', $convertedStr);
        $convertedStr = str_replace('Ã', 'A', $convertedStr);
        $convertedStr = str_replace('Ä', 'A', $convertedStr);
        $convertedStr = str_replace('Å', 'A', $convertedStr);
        $convertedStr = str_replace('ì', 'i', $convertedStr);
        $convertedStr = str_replace('í', 'i', $convertedStr);
        $convertedStr = str_replace('î', 'i', $convertedStr);
        $convertedStr = str_replace('ï', 'i', $convertedStr);
        $convertedStr = str_replace('Ì', 'I', $convertedStr);
        $convertedStr = str_replace('Í', 'I', $convertedStr);
        $convertedStr = str_replace('Î', 'I', $convertedStr);
        $convertedStr = str_replace('Ï', 'I', $convertedStr);
        $convertedStr = str_replace('ð', 'o', $convertedStr);
        $convertedStr = str_replace('ò', 'o', $convertedStr);
        $convertedStr = str_replace('ó', 'o', $convertedStr);
        $convertedStr = str_replace('ô', 'o', $convertedStr);
        $convertedStr = str_replace('õ', 'o', $convertedStr);
        $convertedStr = str_replace('ö', 'o', $convertedStr);
        $convertedStr = str_replace('Ò', 'O', $convertedStr);
        $convertedStr = str_replace('Ó', 'O', $convertedStr);
        $convertedStr = str_replace('Ô', 'O', $convertedStr);
        $convertedStr = str_replace('Õ', 'O', $convertedStr);
        $convertedStr = str_replace('Ö', 'O', $convertedStr);
        $convertedStr = str_replace('ù', 'u', $convertedStr);
        $convertedStr = str_replace('ú', 'u', $convertedStr);
        $convertedStr = str_replace('û', 'u', $convertedStr);
        $convertedStr = str_replace('ü', 'u', $convertedStr);
        $convertedStr = str_replace('Ù', 'U', $convertedStr);
        $convertedStr = str_replace('Ú', 'U', $convertedStr);
        $convertedStr = str_replace('Û', 'U', $convertedStr);
        $convertedStr = str_replace('Ü', 'U', $convertedStr);
        $convertedStr = str_replace('ý', 'y', $convertedStr);
        $convertedStr = str_replace('ÿ', 'y', $convertedStr);
        $convertedStr = str_replace('Ý', 'Y', $convertedStr);

        return $convertedStr;
    }

    public function getSharedEntryUsers(Entry $entry)
    {
        $users = [];
        $entryUsers = $this->entryUserRepo->findBy(['entry' => $entry, 'shared' => true]);

        foreach ($entryUsers as $entryUser) {
            $users[] = $entryUser->getUser();
        }

        return $users;
    }

    public function switchEntryUserShared(Entry $entry, User $user, $shared)
    {
        $this->om->startFlushSuite();
        $entryUser = $this->getEntryUser($entry, $user);
        $entryUser->setShared($shared);
        $this->om->persist($entryUser);
        $this->om->endFlushSuite();
    }

    public function shareEntryWithUsers(Entry $entry, array $usersIds)
    {
        $this->om->startFlushSuite();

        foreach ($usersIds as $userId) {
            $user = $this->userManager->getUserById($userId);

            if (!empty($user)) {
                $this->switchEntryUserShared($entry, $user, true);
            }
        }
        $this->om->endFlushSuite();
    }

    public function getUserEntries(ClacoForm $clacoForm, User $user)
    {
        $entries = [];
        $userEntries = $this->getEntriesByUser($clacoForm, $user);
        $sharedEntryUser = $this->entryUserRepo->findSharedEntryUserByClacoFormAndUser($clacoForm, $user);

        foreach ($userEntries as $entry) {
            $entries[$entry->getId()] = $entry;
        }
        foreach ($sharedEntryUser as $entryUser) {
            $entry = $entryUser->getEntry();
            $entries[$entry->getId()] = $entry;
        }

        return array_values($entries);
    }

    public function generateSharedEntriesData(ClacoForm $clacoForm)
    {
        $data = [];
        $sharedEntriesUsers = $this->entryUserRepo->findSharedEntriesUsersByClacoForm($clacoForm);

        foreach ($sharedEntriesUsers as $entryUser) {
            $entryId = $entryUser->getEntry()->getId();
            $userId = $entryUser->getUser()->getId();

            if (!isset($data[$entryId])) {
                $data[$entryId] = [];
            }
            $data[$entryId][$userId] = true;
        }

        return $data;
    }

    public function deleteAllEntries(ClacoForm $clacoForm)
    {
        $entries = $this->getAllEntries($clacoForm);
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            $fieldValues = $entry->getFieldValues();

            foreach ($fieldValues as $fieldValue) {
                $fieldFacetValue = $fieldValue->getFieldFacetValue();
                $this->om->remove($fieldFacetValue);
                $this->om->remove($fieldValue);
            }
            $this->om->remove($entry);
        }
        $this->om->endFlushSuite();
    }

    public function copyClacoForm(ClacoForm $clacoForm, ResourceNode $newNode)
    {
        $categoryLinks = [];
        $keywordLinks = [];
        $fieldLinks = [];
        $fieldFacetLinks = [];
        $categories = $clacoForm->getCategories();
        $keywords = $clacoForm->getKeywords();
        $fields = $clacoForm->getFields();
        $entries = $this->getAllEntries($clacoForm);

        $newClacoForm = $this->copyResource($clacoForm);

        foreach ($categories as $category) {
            $newCategory = $this->copyCategory($newClacoForm, $category);
            $categoryLinks[$category->getId()] = $newCategory;
        }
        foreach ($keywords as $keyword) {
            $newKeyword = $this->copyKeyword($newClacoForm, $keyword);
            $keywordLinks[$keyword->getId()] = $newKeyword;
        }
        foreach ($fields as $field) {
            $links = $this->copyField($newClacoForm, $newNode, $field, $categoryLinks);

            foreach ($links['fields'] as $key => $value) {
                $fieldLinks[$key] = $value;
            }
            foreach ($links['fieldFacets'] as $key => $value) {
                $fieldFacetLinks[$key] = $value;
            }
        }
        foreach ($entries as $entry) {
            $this->copyEntry($newClacoForm, $entry, $categoryLinks, $keywordLinks, $fieldLinks, $fieldFacetLinks);
        }

        return $newClacoForm;
    }

    private function copyResource(ClacoForm $clacoForm)
    {
        $newClacoForm = new ClacoForm();
        $newClacoForm->setName($clacoForm->getName());
        $newClacoForm->setTemplate($clacoForm->getTemplate());
        $newClacoForm->setDetails($clacoForm->getDetails());
        $this->om->persist($newClacoForm);

        return $newClacoForm;
    }

    private function copyCategory(ClacoForm $newClacoForm, Category $category)
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

    private function copyKeyword(ClacoForm $newClacoForm, Keyword $keyword)
    {
        $newKeyword = new Keyword();
        $newKeyword->setClacoForm($newClacoForm);
        $newKeyword->setName($keyword->getName());
        $this->om->persist($newKeyword);

        return $newKeyword;
    }

    private function copyField(ClacoForm $newClacoForm, ResourceNode $newNode, Field $field, array $categoryLinks)
    {
        $links = [
            'fields' => [],
            'fieldFacets' => [],
            'fieldFacetChoices' => [],
        ];
        $newField = new Field();
        $newField->setClacoForm($newClacoForm);
        $newField->setName($field->getName());
        $newField->setType($field->getType());
        $newField->setRequired($field->isRequired());
        $newField->setIsMetadata($field->getIsMetadata());
        $newField->setLocked($field->isLocked());
        $newField->setLockedEditionOnly($field->getLockedEditionOnly());
        $newField->setHidden($field->isHidden());

        $fieldFacet = $field->getFieldFacet();
        $newFieldFacet = new FieldFacet();
        $newFieldFacet->setName($fieldFacet->getName());
        $newFieldFacet->setType($fieldFacet->getType());
        $newFieldFacet->setPosition($fieldFacet->getPosition());
        $newFieldFacet->setIsRequired($fieldFacet->isRequired());
        $newFieldFacet->setIsEditable($fieldFacet->isEditable());
        $newFieldFacet->setResourceNode($newNode);
        $this->om->persist($newFieldFacet);
        $links['fieldFacets'][$fieldFacet->getId()] = $newFieldFacet;
        $newField->setFieldFacet($newFieldFacet);
        $this->om->persist($newField);
        $links['fields'][$field->getId()] = $newField;

        $fieldFacetChoices = $fieldFacet->getFieldFacetChoices()->toArray();

        foreach ($fieldFacetChoices as $fieldFacetChoice) {
            $newFieldFacetChoice = new FieldFacetChoice();
            $newFieldFacetChoice->setFieldFacet($newFieldFacet);
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
            $choice = $fieldChoiceCategory->getFieldFacetChoice();
            $categoryId = $fieldChoiceCategory->getCategory()->getId();

            if (isset($categoryLinks[$categoryId])) {
                $newFieldChoiceCategory = new FieldChoiceCategory();
                $newFieldChoiceCategory->setField($newField);
                $newFieldChoiceCategory->setValue($fieldChoiceCategory->getValue());
                $newFieldChoiceCategory->setCategory($categoryLinks[$categoryId]);

                if (!empty($choice) && isset($fieldFacetChoiceLinks[$choice->getId()])) {
                    $newFieldChoiceCategory->setFieldFacetChoice($fieldFacetChoiceLinks[$choice->getId()]);
                }
                $this->om->persist($newFieldChoiceCategory);
            }
        }

        return $links;
    }

    private function copyEntry(
        ClacoForm $newClacoForm,
        Entry $entry,
        array $categoryLinks,
        array $keywordLinks,
        array $fieldLinks,
        array $fieldFacetLinks
    ) {
        $categories = $entry->getCategories();
        $keywords = $entry->getKeywords();
        $comments = $entry->getComments();
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
        foreach ($keywords as $keyword) {
            if (isset($keywordLinks[$keyword->getId()])) {
                $newEntry->addKeyword($keywordLinks[$keyword->getId()]);
            }
        }
        $this->om->persist($newEntry);

        foreach ($comments as $comment) {
            $this->copyComment($newEntry, $comment);
        }
        foreach ($fieldValues as $fieldValue) {
            $this->copyFieldValue($newEntry, $fieldValue, $fieldLinks, $fieldFacetLinks);
        }
    }

    private function copyComment(Entry $newEntry, Comment $comment)
    {
        $newComment = new Comment();
        $newComment->setEntry($newEntry);
        $newComment->setUser($comment->getUser());
        $newComment->setStatus($comment->getStatus());
        $newComment->setContent($comment->getContent());
        $newComment->setCreationDate($comment->getCreationDate());
        $newComment->setEditionDate($comment->getEditionDate());
        $this->om->persist($newComment);
    }

    private function copyFieldValue(Entry $newEntry, FieldValue $fieldValue, array $fieldLinks, array $fieldFacetLinks)
    {
        $fieldId = $fieldValue->getField()->getId();
        $fieldFacetValue = $fieldValue->getFieldFacetValue();
        $fieldFacetId = $fieldFacetValue->getFieldFacet()->getId();

        if (isset($fieldLinks[$fieldId]) && isset($fieldFacetLinks[$fieldFacetId])) {
            $newFieldFacetValue = new FieldFacetValue();
            $newFieldFacetValue->setFieldFacet($fieldFacetLinks[$fieldFacetId]);
            $newFieldFacetValue->setUser($fieldFacetValue->getUser());
            $newFieldFacetValue->setArrayValue($fieldFacetValue->getArrayValue());
            $newFieldFacetValue->setDateValue($fieldFacetValue->getDateValue());
            $newFieldFacetValue->setFloatValue($fieldFacetValue->getFloatValue());
            $newFieldFacetValue->setStringValue($fieldFacetValue->getStringValue());
            $this->om->persist($newFieldFacetValue);

            $newFieldValue = new FieldValue();
            $newFieldValue->setEntry($newEntry);
            $newFieldValue->setField($fieldLinks[$fieldId]);
            $newFieldValue->setFieldFacetValue($newFieldFacetValue);
            $this->om->persist($newFieldValue);
        }
    }

    /*****************************************
     * Access to ClacoFormRepository methods *
     *****************************************/

    public function getClacoFormByResourceNode(ResourceNode $resourceNode)
    {
        return $this->clacoFormRepo->findOneBy(['resourceNode' => $resourceNode]);
    }

    public function getClacoFormByResourceNodeId($resourceNodeId)
    {
        return $this->clacoFormRepo->findClacoFormByResourceNodeId($resourceNodeId);
    }

    public function getClacoFormById($id)
    {
        return $this->clacoFormRepo->findOneById($id);
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

    public function getCategoriesByIds(array $ids)
    {
        return count($ids) > 0 ? $this->categoryRepo->findCategoriesByIds($ids) : [];
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

    public function isCategoryManager(ClacoForm $clacoForm, User $user)
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
        $isAnon = $user === 'anon.';
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || (
            $canOpen && (
               ($entry->getUser() === $user) ||
               (!$isAnon && $this->isEntryManager($entry, $user)) ||
               (($entry->getStatus() === Entry::PUBLISHED) && $clacoForm->getSearchEnabled()) ||
               (!$isAnon && $this->isEntryShared($entry, $user))
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
        $isAnon = $user === 'anon.';
        $isEntryShared = $isAnon ? false : $this->isEntryShared($entry, $user);

        return $canEdit || (
            $canOpen && (
                ($editionEnabled && ($entry->getUser() === $user || $isEntryShared)) ||
                (!$isAnon && $this->isEntryManager($entry, $user))
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

    public function isEntryShared(Entry $entry, User $user)
    {
        $entryUser = $this->entryUserRepo->findOneBy(['entry' => $entry, 'user' => $user, 'shared' => true]);

        return !empty($entryUser);
    }

    public function hasEntryOwnership(Entry $entry)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = $user === 'anon.';
        $isOwner = !empty($entry->getUser()) && !$isAnon && $entry->getUser()->getId() === $user->getId();
        $isShared = $isAnon ? false : $this->isEntryShared($entry, $user);

        return $isOwner || $isShared;
    }

    public function checkEntryShareRight(Entry $entry)
    {
        if (!$this->hasRight($entry->getClacoForm(), 'EDIT') && !$this->hasEntryOwnership($entry)) {
            throw new AccessDeniedException();
        }
    }

    private function registerFile(ClacoForm $clacoForm, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = $this->utils->generateGuid();
        $dir = $this->filesDir.$ds.'clacoform'.$ds.$clacoForm->getId();
        $fileName = $hashName.'.'.$file->getClientOriginalExtension();

        $file->move($dir, $fileName);

        return [
            'name' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'url' => '../files/clacoform'.$ds.$clacoForm->getId().$ds.$fileName,
        ];
    }

    private function filterFieldFiles($filedId, array $files = [])
    {
        $filteredFiles = [];

        foreach ($files as $key => $value) {
            $keyParts = explode('-', $key);

            if (count($keyParts) > 0 && intval($keyParts[0]) === intval($filedId)) {
                $filteredFiles[] = $value;
            }
        }

        return $filteredFiles;
    }

    private function removeOldFiles(array $oldFiles, array $newFiles)
    {
        foreach ($oldFiles as $oldFile) {
            $isPresent = false;

            foreach ($newFiles as $newFile) {
                if ($newFile['url'] === $oldFile['url']) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                $this->fileSystem->remove($this->filesDir.DIRECTORY_SEPARATOR.$oldFile['url']);
            }
        }
    }
}
