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
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\EntryUser;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Repository\EntryRepository;
use Claroline\ClacoFormBundle\Repository\EntryUserRepository;
use Claroline\ClacoFormBundle\Repository\FieldValueRepository;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClacoFormManager
{
    private UserRepository $userRepo;
    private EntryRepository $entryRepo;
    private EntryUserRepository $entryUserRepo;
    private FieldValueRepository $fieldValueRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly string $filesDir,
        private readonly ObjectManager $om,
        private readonly RouterInterface $router,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $messageBus,
        private readonly CategoryManager $categoryManager
    ) {
        $this->userRepo = $om->getRepository(User::class);
        $this->entryRepo = $om->getRepository(Entry::class);
        $this->entryUserRepo = $om->getRepository(EntryUser::class);
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

    public function changeEntriesStatus(array $entries, $status)
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

    public function switchEntriesLock(array $entries, $locked)
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

    public function notifyPendingComment(Entry $entry, Comment $comment): void
    {
        $clacoForm = $entry->getClacoForm();
        $node = $clacoForm->getResourceNode();

        if ($clacoForm->getDisplayComments()) {
            $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).
                '#/desktop/resources/'.$node->getSlug().'/entries/'.$entry->getUuid();
            $receiverIds = [];
            $categories = $entry->getCategories();

            foreach ($categories as $category) {
                if ($category->getNotifyPendingComment()) {
                    $managers = $category->getManagers();

                    foreach ($managers as $manager) {
                        if (!in_array($manager->getId(), $receiverIds)) {
                            $receiverIds[] = $manager->getId();
                        }
                    }
                }
            }

            if (count($receiverIds) > 0) {
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

                $this->messageBus->dispatch(new SendMessage($content, $object, $receiverIds));
            }
        }
    }

    public function persistComment(Comment $comment): void
    {
        $this->om->persist($comment);
        $this->om->flush();
    }

    public function createComment(Entry $entry, $content, ?User $user = null): Comment
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

        if (Comment::VALIDATED === $comment->getStatus()) {
            $this->notifyUsers($entry, 'comment', $content);
        } else {
            $this->notifyPendingComment($entry, $comment);
        }

        return $comment;
    }

    public function editComment(Comment $comment, $content): Comment
    {
        $comment->setContent($content);
        $comment->setEditionDate(new \DateTime());
        $this->persistComment($comment);

        if (Comment::VALIDATED === $comment->getStatus()) {
            $this->notifyUsers($comment->getEntry(), 'comment', $content);
        }

        return $comment;
    }

    public function changeCommentStatus(Comment $comment, $status): Comment
    {
        $comment->setStatus($status);
        $this->persistComment($comment);

        if (Comment::VALIDATED === $comment->getStatus()) {
            $this->notifyUsers($comment->getEntry(), 'comment', $comment->getContent());
        }

        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        $this->om->remove($comment);
        $this->om->flush();
    }

    public function createEntryUser(Entry $entry, User $user, bool $shared = false, bool $notifyEdition = false, bool $notifyComment = false, bool $notifyVote = false): EntryUser
    {
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

    public function getEntryUser(Entry $entry, User $user): EntryUser
    {
        $entryUser = $this->entryUserRepo->findOneBy(['entry' => $entry, 'user' => $user]);

        if (empty($entryUser)) {
            $entryUser = $this->createEntryUser($entry, $user);
        }

        return $entryUser;
    }

    public function notifyUsers(Entry $entry, $type, $data = null): void
    {
        $sendMessage = false;
        $receiverIds = [];
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
                    $receiverIds[] = $entryUser->getUser()->getId();
                }
                if ($sendMessage && count($receiverIds) > 0) {
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
                    $receiverIds[] = $entryUser->getUser()->getId();
                }
                if ($sendMessage && count($receiverIds) > 0) {
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
                        if ($entryUser->getUser()) {
                            $roles = array_intersect($commentsRoles, $user->getRoles());

                            if (count($roles) > 0) {
                                $receiverIds[] = $user->getId();
                            }
                        }
                    }
                    if (count($receiverIds) > 0) {
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

        if ($sendMessage && count($receiverIds) > 0) {
            $this->messageBus->dispatch(new SendMessage($content, $subject, $receiverIds));
        }
    }

    public function switchEntryUserShared(Entry $entry, User $user, $shared): void
    {
        $this->om->startFlushSuite();
        $entryUser = $this->getEntryUser($entry, $user);
        $entryUser->setShared($shared);
        $this->om->persist($entryUser);
        $this->om->endFlushSuite();
    }

    public function shareEntryWithUsers(Entry $entry, array $usersIds): void
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

    public function getUserEntries(ClacoForm $clacoForm, User $user): array
    {
        $entries = [];
        $userEntries = $this->entryRepo->findBy(['clacoForm' => $clacoForm, 'user' => $user]);
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

    // TODO : use serializers
    public function copyClacoForm(ClacoForm $clacoForm, ClacoForm $newClacoForm): ClacoForm
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

    private function copyKeyword(ClacoForm $newClacoForm, Keyword $keyword): Keyword
    {
        $newKeyword = new Keyword();
        $newKeyword->setClacoForm($newClacoForm);
        $newKeyword->setName($keyword->getName());
        $this->om->persist($newKeyword);

        return $newKeyword;
    }

    private function copyField(ClacoForm $newClacoForm, ResourceNode $newNode, Field $field, array $categoryLinks): array
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
        array $keywordLinks,
        array $fieldLinks,
        array $fieldFacetLinks
    ): void {
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

    private function copyComment(Entry $newEntry, Comment $comment): void
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

    // TODO : move to Voter
    public function hasEntryAccessRight(Entry $entry): bool
    {
        $clacoForm = $entry->getClacoForm();
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || (
            $canOpen && (
                ($entry->getUser() === $user)
                || (!$isAnon && $this->isEntryManager($entry, $user))
                || ((Entry::PUBLISHED === $entry->getStatus()) && $clacoForm->getSearchEnabled())
                || (!$isAnon && $this->isEntryShared($entry, $user))
            )
        );
    }

    // TODO : move to Voter
    public function hasEntryModerationRight(Entry $entry): bool
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $clacoForm = $entry->getClacoForm();
        $canOpen = $this->hasRight($clacoForm, 'OPEN');
        $canEdit = $this->hasRight($clacoForm, 'EDIT');

        return $canEdit || ($canOpen && $user instanceof User && $this->isEntryManager($entry, $user));
    }

    public function checkEntryAccess(Entry $entry): void
    {
        if (!$this->hasEntryAccessRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkEntryModeration(Entry $entry): void
    {
        if (!$this->hasEntryModerationRight($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function checkCommentCreationRight(Entry $entry): void
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

    public function checkCommentEditionRight(Comment $comment): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $entry = $comment->getEntry();
        $clacoForm = $entry->getClacoForm();

        if (!$this->hasEntryAccessRight($entry)
            || !$clacoForm->isCommentsEnabled()
            || (($user !== $comment->getUser()) && !$this->hasEntryModerationRight($entry))
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

    public function isEntryShared(Entry $entry, User $user): bool
    {
        $entryUser = $this->entryUserRepo->findOneBy(['entry' => $entry, 'user' => $user, 'shared' => true]);

        return !empty($entryUser);
    }

    private function hasEntryOwnership(Entry $entry): bool
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
        $isOwner = !empty($entry->getUser()) && !$isAnon && $entry->getUser()->getId() === $user->getId();
        $isShared = $isAnon ? false : $this->isEntryShared($entry, $user);

        return $isOwner || $isShared;
    }

    public function checkEntryShareRight(Entry $entry): void
    {
        if (!$this->hasRight($entry->getClacoForm(), 'EDIT') && !$this->hasEntryOwnership($entry)) {
            throw new AccessDeniedException();
        }
    }

    public function canViewComments(ClacoForm $clacoForm): bool
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
