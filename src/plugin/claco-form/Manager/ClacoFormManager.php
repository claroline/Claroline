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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\EntryUser;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Event\Log\LogClacoFormConfigureEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentCreateEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentEditEvent;
use Claroline\ClacoFormBundle\Event\Log\LogCommentStatusChangeEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryDeleteEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryLockSwitchEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryStatusChangeEvent;
use Claroline\ClacoFormBundle\Event\Log\LogEntryUserChangeEvent;
use Claroline\ClacoFormBundle\Event\Log\LogKeywordCreateEvent;
use Claroline\ClacoFormBundle\Repository\CategoryRepository;
use Claroline\ClacoFormBundle\Repository\ClacoFormRepository;
use Claroline\ClacoFormBundle\Repository\CommentRepository;
use Claroline\ClacoFormBundle\Repository\EntryRepository;
use Claroline\ClacoFormBundle\Repository\EntryUserRepository;
use Claroline\ClacoFormBundle\Repository\FieldRepository;
use Claroline\ClacoFormBundle\Repository\FieldValueRepository;
use Claroline\ClacoFormBundle\Repository\KeywordRepository;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\MessageBundle\Manager\MessageManager;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class ClacoFormManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Filesystem */
    private $fileSystem;
    /** @var string */
    private $filesDir;
    /** @var MessageManager */
    private $messageManager;
    /** @var ObjectManager */
    private $om;
    /** @var RouterInterface */
    private $router;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;

    /** @var UserRepository */
    private $userRepo;
    /** @var CategoryRepository */
    private $categoryRepo;
    /** @var ClacoFormRepository */
    private $clacoFormRepo;
    /** @var CommentRepository */
    private $commentRepo;
    /** @var EntryRepository */
    private $entryRepo;
    /** @var EntryUserRepository */
    private $entryUserRepo;
    private $fieldChoiceCategoryRepo;
    /** @var FieldRepository */
    private $fieldRepo;
    /** @var FieldValueRepository */
    private $fieldValueRepo;
    /** @var KeywordRepository */
    private $keywordRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        Filesystem $fileSystem,
        string $filesDir,
        MessageManager $messageManager,
        ObjectManager $om,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSystem = $fileSystem;
        $this->filesDir = $filesDir;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->messageBus = $messageBus;

        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->categoryRepo = $om->getRepository('ClarolineClacoFormBundle:Category');
        $this->clacoFormRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoForm');
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
            'date',
            'user',
            'categories',
            'keywords',
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
        $clacoForm->setCommentsRoles(['ROLE_USER']);
        $clacoForm->setCommentsDisplayRoles(['ROLE_USER']);

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

        $clacoForm->setTitleFieldLabel(null);

        $clacoForm->setSearchRestricted(false);
        $clacoForm->setSearchRestrictedColumns(['title']);

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
        $this->eventDispatcher->dispatch($event, 'log');

        return $details;
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
            $this->eventDispatcher->dispatch($event, 'log');
        }

        return $keyword;
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
                $canCreate = $clacoForm->isCreationEnabled() && (0 === $maxEntries);
            } else {
                $userEntries = $this->getEntriesByUser($clacoForm, $user);
                $canCreate = $clacoForm->isCreationEnabled() && ((0 === $maxEntries) || ($maxEntries > count($userEntries)));
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
            $entryId = $entries[$randomIndex]->getUuid();
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

    public function getAllUsedCountriesCodes(ClacoForm $clacoForm)
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
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function deleteEntries(array $entries)
    {
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            $this->deleteEntry($entry);
        }
        $this->om->endFlushSuite();
    }

    public function changeEntryStatus(Entry $entry)
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
        $event = new LogEntryStatusChangeEvent($entry);
        $this->eventDispatcher->dispatch($event, 'log');
        $categories = $entry->getCategories();
        $this->notifyCategoriesManagers($entry, $categories, $categories);

        return $entry;
    }

    public function changeEntriesStatus(array $entries, $status)
    {
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            if (Entry::PUBLISHED === $status) {
                $entry->setPublicationDate(new \DateTime());
            }
            $entry->setStatus($status);
            $this->persistEntry($entry);
            $event = new LogEntryStatusChangeEvent($entry);
            $this->eventDispatcher->dispatch($event, 'log');
            $categories = $entry->getCategories();
            $this->notifyCategoriesManagers($entry, $categories, $categories);
        }
        $this->om->endFlushSuite();

        return $entries;
    }

    public function switchEntryLock(Entry $entry)
    {
        $locked = $entry->isLocked();
        $entry->setLocked(!$locked);
        $this->persistEntry($entry);
        $event = new LogEntryLockSwitchEvent($entry);
        $this->eventDispatcher->dispatch($event, 'log');
        $categories = $entry->getCategories();
        $this->notifyCategoriesManagers($entry, $categories, $categories);

        return $entry;
    }

    public function switchEntriesLock(array $entries, $locked)
    {
        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            $entry->setLocked($locked);
            $this->persistEntry($entry);
            $event = new LogEntryLockSwitchEvent($entry);
            $this->eventDispatcher->dispatch($event, 'log');
            $categories = $entry->getCategories();
            $this->notifyCategoriesManagers($entry, $categories, $categories);
        }
        $this->om->endFlushSuite();

        return $entries;
    }

    public function changeEntryOwner(Entry $entry, User $user)
    {
        $entry->setUser($user);
        $this->persistEntry($entry);
        $event = new LogEntryUserChangeEvent($entry);
        $this->eventDispatcher->dispatch($event, 'log');

        return $entry;
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

    public function notifyPendingComment(Entry $entry, Comment $comment)
    {
        $clacoForm = $entry->getClacoForm();
        $node = $clacoForm->getResourceNode();

        if ($clacoForm->getDisplayComments()) {
            $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
                '#/desktop/resources/'.$node->getSlug().'/entries/'.$entry->getUuid();
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
                    $node->getName();
                $content = $comment->getContent().
                    '<br><br>'.
                    $this->translator->trans('link_to_entry', [], 'clacoform').
                    ' : <a href="'.$url.'">'.
                    $this->translator->trans('here', [], 'platform').
                    '</a><br><br>';

                $this->messageBus->dispatch(new SendMessage($content, $object, $receivers));
            }
        }
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
        $this->eventDispatcher->dispatch($event, 'log');

        if (Comment::VALIDATED === $comment->getStatus()) {
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
        $this->eventDispatcher->dispatch($event, 'log');

        if (Comment::VALIDATED === $comment->getStatus()) {
            $this->notifyUsers($comment->getEntry(), 'comment', $content);
        }

        return $comment;
    }

    public function changeCommentStatus(Comment $comment, $status)
    {
        $comment->setStatus($status);
        $this->persistComment($comment);
        $event = new LogCommentStatusChangeEvent($comment);
        $this->eventDispatcher->dispatch($event, 'log');

        if (Comment::VALIDATED === $comment->getStatus()) {
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
        $this->eventDispatcher->dispatch($event, 'log');
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
        $node = $clacoForm->getResourceNode();
        $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
            '#desktop/resources/'.$node->getSlug().'/entries/'.$entry->getUuid();

        $subject = '';
        $content = '';
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
                        $node->getName();
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
                        $node->getName();
                    $content = $this->translator->trans('entry_deletion_msg', ['%title%' => $entry->getTitle()], 'clacoform');
                }
                break;
            case 'comment':
                $sendMessage = $clacoForm->getDisplayComments();
                $commentsRoles = $clacoForm->getCommentsDisplayRoles();

                if ($sendMessage && count($commentsRoles) > 0) {
                    /** @var EntryUser[] $entryUsers */
                    $entryUsers = $this->entryUserRepo->findBy(['entry' => $entry, 'notifyComment' => true]);

                    foreach ($entryUsers as $entryUser) {
                        $user = $entryUser->getUser();
                        $roles = array_intersect($commentsRoles, $user->getRoles());

                        if (count($roles) > 0) {
                            $receivers[] = $user;
                        }
                    }
                    if (count($receivers) > 0) {
                        $subject = '['.
                            $this->translator->trans('entry_comment', [], 'clacoform').
                            '] '.
                            $entry->getTitle().
                            ' - '.
                            $node->getName();
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
            $this->messageBus->dispatch(new SendMessage($content, $subject, $receivers));
        }
    }

    public function hasFiles(ClacoForm $clacoForm)
    {
        $hasFiles = false;
        $fields = $clacoForm->getFields();

        foreach ($fields as $field) {
            if (FieldFacet::FILE_TYPE === $field->getType()) {
                $hasFiles = true;
                break;
            }
        }

        return $hasFiles;
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
            /** @var User $user */
            $user = $this->userRepo->findOneBy(['uuid' => $userId]);
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

    public function copyClacoForm(ClacoForm $clacoForm, ClacoForm $newClacoForm)
    {
        $categoryLinks = [];
        $keywordLinks = [];
        $fieldLinks = [];
        $fieldFacetLinks = [];
        $categories = $clacoForm->getCategories();
        $keywords = $clacoForm->getKeywords();
        $fields = $clacoForm->getFields();
        $entries = $this->getAllEntries($clacoForm);

        foreach ($categories as $category) {
            $newCategory = $this->copyCategory($newClacoForm, $category);
            $categoryLinks[$category->getId()] = $newCategory;
        }
        foreach ($keywords as $keyword) {
            $newKeyword = $this->copyKeyword($newClacoForm, $keyword);
            $keywordLinks[$keyword->getId()] = $newKeyword;
        }
        foreach ($fields as $field) {
            $links = $this->copyField($newClacoForm, $newClacoForm->getResourceNode(), $field, $categoryLinks);

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
        $template = $clacoForm->getTemplate();

        if ($template) {
            foreach ($fieldLinks as $key => $value) {
                $template = str_replace("%field_$key%", '%field_'.$value->getUuid().'%', $template);
            }
            $newClacoForm->setTemplate($template);
        }

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
        $newField->setDetails($field->getDetails());
        $newField->setFileTypes($field->getFileTypes());
        $newField->setHelp($field->getHelp());
        $newField->setNbFilesMax($field->getNbFilesMax());
        $newField->setOrder($field->getOrder());

        $fieldFacet = $field->getFieldFacet();
        $newFieldFacet = new FieldFacet();
        $newFieldFacet->setLabel($fieldFacet->getLabel());
        $newFieldFacet->setType($fieldFacet->getType());
        $newFieldFacet->setPosition($fieldFacet->getPosition());
        $newFieldFacet->setRequired($fieldFacet->isRequired());
        $newFieldFacet->setResourceNode($newNode);
        $this->om->persist($newFieldFacet);
        $links['fieldFacets'][$fieldFacet->getId()] = $newFieldFacet;
        $newField->setFieldFacet($newFieldFacet);
        $this->om->persist($newField);
        $links['fields'][$field->getUuid()] = $newField;

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
        $fieldId = $fieldValue->getField()->getUuid();
        $fieldFacetValue = $fieldValue->getFieldFacetValue();
        $fieldFacetId = $fieldFacetValue->getFieldFacet()->getId();

        if (isset($fieldLinks[$fieldId]) && isset($fieldFacetLinks[$fieldFacetId])) {
            $newFieldFacetValue = new FieldFacetValue();
            $newFieldFacetValue->setFieldFacet($fieldFacetLinks[$fieldFacetId]);
            $newFieldFacetValue->setUser($fieldFacetValue->getUser());
            $newFieldFacetValue->setArrayValue($fieldFacetValue->getArrayValue());
            $newFieldFacetValue->setDateValue($fieldFacetValue->getDateValue(false));
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

    /****************************************
     * Access to CategoryRepository methods *
     ****************************************/

    public function getCategoriesByIds(array $ids)
    {
        return count($ids) > 0 ? $this->categoryRepo->findCategoriesByIds($ids) : [];
    }

    /*************************************
     * Access to FieldRepository methods *
     *************************************/

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

    public function getFieldValuesByType(ClacoForm $clacoForm, $type)
    {
        return $this->fieldValueRepo->findFieldValuesByType($clacoForm, $type);
    }

    /***************************************
     * Access to KeywordRepository methods *
     ***************************************/

    public function getKeywordByName(ClacoForm $clacoForm, $name)
    {
        return $this->keywordRepo->findKeywordByName($clacoForm, $name);
    }

    public function getKeywordByNameExcludingUuid(ClacoForm $clacoForm, $name, $uuid)
    {
        return $this->keywordRepo->findKeywordByNameExcludingUuid($clacoForm, $name, $uuid);
    }

    /*************************************
     * Access to EntryRepository methods *
     *************************************/

    public function getEntriesByUser(ClacoForm $clacoForm, User $user)
    {
        return $this->entryRepo->findBy(['clacoForm' => $clacoForm, 'user' => $user]);
    }

    /**
     * @return Entry[]|ArrayCollection
     */
    public function getAllEntries(ClacoForm $clacoForm)
    {
        return $this->entryRepo->findBy(['clacoForm' => $clacoForm]);
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
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || (
            $canOpen && (
               ($entry->getUser() === $user) ||
               (!$isAnon && $this->isEntryManager($entry, $user)) ||
               ((Entry::PUBLISHED === $entry->getStatus()) && $clacoForm->getSearchEnabled()) ||
               (!$isAnon && $this->isEntryShared($entry, $user))
            )
        );
    }

    public function hasEntryEditionRight(Entry $entry)
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');
        $editionEnabled = $clacoForm->isEditionEnabled();
        $isAnon = !$user instanceof User;
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
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || ($canOpen && $user instanceof User && $this->isEntryManager($entry, $user));
    }

    public function checkEntryAccess(Entry $entry)
    {
        if (!$this->hasEntryAccessRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkEntryEdition(Entry $entry)
    {
        if ($entry->isLocked() || !$this->hasEntryEditionRight($entry)) {
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
        $clacoForm = $entry->getClacoForm();

        if (!$this->hasEntryAccessRight($entry) || !$clacoForm->isCommentsEnabled()) {
            throw new AccessDeniedException();
        }

        $commentsRoles = $clacoForm->getCommentsRoles();
        foreach ($commentsRoles as $commentsRole) {
            if (in_array($commentsRole, $this->tokenStorage->getToken()->getRoleNames())) {
                return;
            }
        }
    }

    public function checkCommentEditionRight(Comment $comment)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $entry = $comment->getEntry();
        $clacoForm = $entry->getClacoForm();

        if (!$this->hasEntryAccessRight($entry) ||
            !$clacoForm->isCommentsEnabled() ||
            (($user !== $comment->getUser()) && !$this->hasEntryModerationRight($entry))
        ) {
            throw new AccessDeniedException();
        }
        $userRoles = $user->getRoles();
        $commentsRoles = $clacoForm->getCommentsRoles();

        foreach ($commentsRoles as $commentsRole) {
            if (in_array($commentsRole, $userRoles)) {
                return;
            }
        }
    }

    public function isEntryShared(Entry $entry, User $user)
    {
        $entryUser = $this->entryUserRepo->findOneBy(['entry' => $entry, 'user' => $user, 'shared' => true]);

        return !empty($entryUser);
    }

    public function hasEntryOwnership(Entry $entry)
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
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

    public function canViewComments(ClacoForm $clacoForm)
    {
        $canViewComments = false;

        if ($clacoForm->getDisplayComments()) {
            $commentsDisplayRoles = $clacoForm->getCommentsDisplayRoles();
            foreach ($commentsDisplayRoles as $commentsDisplayRole) {
                if (in_array($commentsDisplayRole, $this->tokenStorage->getToken()->getRoleNames())) {
                    $canViewComments = true;
                    break;
                }
            }
        }

        return $canViewComments;
    }

    public function registerFile(ClacoForm $clacoForm, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString();
        $dir = $this->filesDir.$ds.'clacoform'.$ds.$clacoForm->getUuid();
        $fileName = $hashName.'.'.$file->getClientOriginalExtension();

        $file->move($dir, $fileName);

        return [
            'name' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'url' => '../files/clacoform'.$ds.$clacoForm->getUuid().$ds.$fileName,
        ];
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceCategoryManager(User $from, User $to)
    {
        $categories = $this->categoryRepo->findAllCategoriesByManager($from);

        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $category->removeManager($from);
                $category->addManager($to);
            }

            $this->om->flush();
        }

        return count($categories);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceCommentUser(User $from, User $to)
    {
        $comments = $this->commentRepo->findByUser($from);

        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $comment->setUser($to);
            }

            $this->om->flush();
        }

        return count($comments);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceEntryUser(User $from, User $to)
    {
        /** @var Entry[] $entries */
        $entries = $this->entryRepo->findBy(['user' => $from]);

        if (count($entries) > 0) {
            foreach ($entries as $entry) {
                $entry->setUser($to);
            }

            $this->om->flush();
        }

        return count($entries);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceEntryUserUser(User $from, User $to)
    {
        /** @var EntryUser[] $entryUsers */
        $entryUsers = $this->entryUserRepo->findBy(['user' => $from]);

        if (count($entryUsers) > 0) {
            foreach ($entryUsers as $entryUser) {
                $entryUser->setUser($to);
            }

            $this->om->flush();
        }

        return count($entryUsers);
    }

    /**
     * Creates an entries from data from a csv.
     *
     * @return int
     */
    public function importEntryFromCsv(ClacoForm $clacoForm, User $user, array $data)
    {
        $fieldsMapping = [];
        $categoriesMapping = [];
        $keywordsMapping = [];
        $fields = $clacoForm->getFields();
        $categories = $clacoForm->getCategories();
        $keywords = $clacoForm->getKeywords();

        foreach ($fields as $field) {
            $fieldsMapping[$field->getName()] = $field;
        }
        foreach ($categories as $category) {
            $categoriesMapping[$category->getName()] = $category;
        }
        foreach ($keywords as $keyword) {
            $keywordsMapping[$keyword->getName()] = $keyword;
        }
        if (0 < count($data)) {
            $this->om->startFlushSuite();
            $now = new \DateTime();

            foreach ($data as $index => $entryData) {
                $existingEntries = isset($entryData['title']) ?
                    $this->entryRepo->findBy(['clacoForm' => $clacoForm, 'title' => $entryData['title']]) :
                    null;
                $lineNum = $index + 1;

                if (is_null($existingEntries)) {
                    $this->log("Entry from line {$lineNum} has no title or it is simply an empty line at the end of the file.", LogLevel::WARNING);
                } elseif (0 === count($existingEntries)) {
                    $this->log("Importing entry from line {$lineNum}...");
                    $entry = new Entry();
                    $entry->setUser($user);
                    $entry->setClacoForm($clacoForm);
                    $entry->setStatus(Entry::PUBLISHED);
                    $entry->setCreationDate($now);
                    $entry->setPublicationDate($now);

                    foreach ($entryData as $key => $value) {
                        switch ($key) {
                            case 'title':
                                $entry->setTitle($value);
                                break;
                            case 'status':
                                $entry->setStatus(intval($value));
                                break;
                            case 'categories':
                                $categoriesNames = explode(',', $value);

                                foreach ($categoriesNames as $categoryName) {
                                    if (isset($categoriesMapping[$categoryName])) {
                                        $entry->addCategory($categoriesMapping[$categoryName]);
                                    }
                                }
                                break;
                            case 'keywords':
                                $keywordsNames = explode(',', $value);

                                foreach ($keywordsNames as $keywordName) {
                                    if (isset($keywordsMapping[$keywordName])) {
                                        $entry->addKeyword($keywordsMapping[$keywordName]);
                                    }
                                }
                                break;
                            case 'comments':
                                $contents = explode('|', $value);

                                foreach ($contents as $content) {
                                    $comment = new Comment();
                                    $comment->setEntry($entry);
                                    $comment->setUser($user);
                                    $comment->setContent($content);
                                    $comment->setCreationDate($now);
                                    $comment->setStatus(Comment::VALIDATED);
                                    $this->om->persist($comment);
                                }
                                break;
                            default:
                                if (isset($fieldsMapping[$key])) {
                                    $field = $fieldsMapping[$key];
                                    $fieldFacet = $field->getFieldFacet();
                                    $fieldValue = new FieldValue();
                                    $fieldValue->setEntry($entry);
                                    $fieldValue->setField($field);

                                    $fielFacetValue = new FieldFacetValue();
                                    $fielFacetValue->setUser($user);
                                    $fielFacetValue->setFieldFacet($fieldFacet);

                                    $formattedValue = $value;

                                    switch ($fieldFacet->getType()) {
                                        case FieldFacet::NUMBER_TYPE:
                                            $formattedValue = floatval($value);
                                            break;
                                        case FieldFacet::CASCADE_TYPE:
                                        case FieldFacet::FILE_TYPE:
                                            $formattedValue = explode(',', $value);
                                            break;
                                        case FieldFacet::BOOLEAN_TYPE:
                                            $formattedValue = empty($value) || 'false' === $value ? false : true;
                                            break;
                                        case FieldFacet::CHOICE_TYPE:
                                            $options = $fieldFacet->getOptions();

                                            if (isset($options['multiple']) && $options['multiple']) {
                                                $formattedValue = explode(',', $value);
                                            }
                                            break;
                                    }
                                    $fielFacetValue->setValue($formattedValue);
                                    $this->om->persist($fielFacetValue);

                                    $fieldValue->setFieldFacetValue($fielFacetValue);
                                    $this->om->persist($fieldValue);
                                }
                        }
                    }
                    $this->om->persist($entry);
                } else {
                    $this->log("Entry from line {$lineNum} already existed.", LogLevel::ERROR);
                }
            }
            $this->om->endFlushSuite();
        }
    }
}
