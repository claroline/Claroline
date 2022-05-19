<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\EntryUser;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\LocationManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @Route("/claco/form", options = {"expose"=true})
 */
class ClacoFormController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var ClacoFormManager */
    private $clacoFormManager;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var string */
    private $filesDir;
    /** @var LocationManager */
    private $locationManager;
    /** @var Environment */
    private $templating;
    /** @var TranslatorInterface */
    private $translator;
    /** @var SerializerProvider */
    private $serializer;
    /** @var PdfManager */
    private $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        ClacoFormManager $clacoFormManager,
        PlatformConfigurationHandler $configHandler,
        $filesDir,
        LocationManager $locationManager,
        Environment $templating,
        TranslatorInterface $translator,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage,
        PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->clacoFormManager = $clacoFormManager;
        $this->configHandler = $configHandler;
        $this->filesDir = $filesDir;
        $this->locationManager = $locationManager;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->pdfManager = $pdfManager;
    }

    /**
     * Returns the keyword.
     *
     * @Route("/{clacoForm}/keyword/get/by/name/{value}/excluding/uuid/{uuid}", name="claro_claco_form_get_keyword_by_name_excluding_uuid", defaults={"uuid"=null})
     * @EXT\ParamConverter( "clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function getKeywordByNameExcludingUuidAction(ClacoForm $clacoForm, $value, ?string $uuid = null): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm->getResourceNode(), [], true);

        $keyword = $this->clacoFormManager->getKeywordByNameExcludingUuid($clacoForm, $value, $uuid);

        if (!empty($keyword)) {
            return new JsonResponse(true);
        }

        return new JsonResponse(false, 204);
    }

    /**
     * Returns id of a random entry.
     *
     * @Route("/{clacoForm}/entry/random", name="claro_claco_form_entry_random")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function entryRandomAction(ClacoForm $clacoForm): JsonResponse
    {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $entryId = $this->clacoFormManager->getRandomEntryId($clacoForm);

        return new JsonResponse($entryId, 200);
    }

    /**
     * Retrieves comments of an entry.
     *
     * @Route("/entry/{entry}/comments/retrieve", name="claro_claco_form_entry_comments_retrieve")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entryCommentsRetrieveAction(Entry $entry): JsonResponse
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            $comments = $this->clacoFormManager->getCommentsByEntryAndStatus($entry, Comment::VALIDATED);
        } elseif ($this->clacoFormManager->hasEntryModerationRight($entry)) {
            $comments = $this->clacoFormManager->getCommentsByEntry($entry);
        } else {
            $comments = $this->clacoFormManager->getAvailableCommentsForUser($entry, $user);
        }
        $serializedComments = array_map(function (Comment $comment) {
            return $this->serializer->serialize($comment);
        }, $comments);

        return new JsonResponse($serializedComments, 200);
    }

    /**
     * Creates a comment.
     *
     * @Route("/entry/{entry}/comment/create", name="claro_claco_form_entry_comment_create")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function commentCreateAction(Entry $entry, Request $request): JsonResponse
    {
        $this->clacoFormManager->checkCommentCreationRight($entry);

        $decodedRequest = $this->decodeRequest($request);
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $user = $authenticatedUser instanceof User ? $authenticatedUser : null;

        $comment = $this->clacoFormManager->createComment($entry, $decodedRequest['message'], $user);

        return new JsonResponse($this->serializer->serialize($comment), 200);
    }

    /**
     * Edits a comment.
     *
     * @Route("/entry/comment/{comment}/edit", name="claro_claco_form_entry_comment_edit")
     * @EXT\ParamConverter("comment", class="Claroline\ClacoFormBundle\Entity\Comment", options={"mapping": {"comment": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function commentEditAction(Comment $comment, Request $request): JsonResponse
    {
        $this->clacoFormManager->checkCommentEditionRight($comment);

        $decodedRequest = $this->decodeRequest($request);
        $comment = $this->clacoFormManager->editComment($comment, $decodedRequest['message']);

        return new JsonResponse($this->serializer->serialize($comment), 200);
    }

    /**
     * Deletes a comment.
     *
     * @Route("/entry/comment/{comment}/delete", name="claro_claco_form_entry_comment_delete")
     * @EXT\ParamConverter("comment", class="Claroline\ClacoFormBundle\Entity\Comment", options={"mapping": {"comment": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function commentDeleteAction(Comment $comment): JsonResponse
    {
        $this->clacoFormManager->checkCommentEditionRight($comment);
        $this->clacoFormManager->deleteComment($comment);

        return new JsonResponse(null, 204);
    }

    /**
     * Activates a comment.
     *
     * @Route("/entry/comment/{comment}/activate", name="claro_claco_form_entry_comment_activate")
     * @EXT\ParamConverter("comment", class="Claroline\ClacoFormBundle\Entity\Comment", options={"mapping": {"comment": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function commentActivateAction(Comment $comment): JsonResponse
    {
        $this->clacoFormManager->checkEntryModeration($comment->getEntry());
        $comment = $this->clacoFormManager->changeCommentStatus($comment, Comment::VALIDATED);
        $serializedComment = $this->serializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * Blocks a comment.
     *
     * @Route("/entry/comment/{comment}/block", name="claro_claco_form_entry_comment_block")
     * @EXT\ParamConverter("comment", class="Claroline\ClacoFormBundle\Entity\Comment", options={"mapping": {"comment": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function commentBlockAction(Comment $comment): JsonResponse
    {
        $this->clacoFormManager->checkEntryModeration($comment->getEntry());
        $comment = $this->clacoFormManager->changeCommentStatus($comment, Comment::BLOCKED);
        $serializedComment = $this->serializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * Retrieves an entry options for current user.
     *
     * @Route("/entry/{entry}/user/retrieve", name="claro_claco_form_entry_user_retrieve")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function entryUserRetrieveAction(Entry $entry, User $user): JsonResponse
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $entryUser = $this->clacoFormManager->getEntryUser($entry, $user);
        $serializedEntryUser = $this->serializer->serialize($entryUser);

        return new JsonResponse($serializedEntryUser, 200);
    }

    /**
     * Saves entry options for current user.
     *
     * @Route("/entry/{entry}/user/save", name="claro_claco_form_entry_user_save")
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function entryUserSaveAction(User $user, Entry $entry, Request $request): JsonResponse
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $entryUser = $this->clacoFormManager->getEntryUser($entry, $user);
        $entryUserData = $request->request->get('entryUserData', false);

        if (!is_array($entryUserData)) {
            $entryUserData = json_decode($entryUserData, true);
        }

        if (isset($entryUserData['shared'])) {
            $entryUser->setShared($entryUserData['shared']);
        }
        if (isset($entryUserData['notifyEdition'])) {
            $entryUser->setNotifyEdition($entryUserData['notifyEdition']);
        }
        if (isset($entryUserData['notifyComment'])) {
            $entryUser->setNotifyComment($entryUserData['notifyComment']);
        }
        if (isset($entryUserData['notifyVote'])) {
            $entryUser->setNotifyVote($entryUserData['notifyVote']);
        }
        $this->clacoFormManager->persistEntryUser($entryUser);

        return new JsonResponse(null, 204);
    }

    /**
     * Downloads pdf version of entry.
     *
     * @Route("/entry/{entry}/pdf/download", name="claro_claco_form_entry_pdf_download")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function entryPdfDownloadAction(Entry $entry, User $user): StreamedResponse
    {
        $this->clacoFormManager->checkEntryAccess($entry);

        $fileName = TextNormalizer::toKey($entry->getTitle());

        return new StreamedResponse(function () use ($entry, $user) {
            echo $this->pdfManager->fromHtml(
                $this->generatePdfForEntry($entry, $user)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }

    /**
     * Retrieves list of users the entry is shared with.
     *
     * @Route("/entry/{entry}/shared/users/list", name="claro_claco_form_entry_shared_users_list")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entrySharedUsersListAction(Entry $entry): JsonResponse
    {
        $this->clacoFormManager->checkEntryShareRight($entry);

        $results = $this->finder->searchEntities(EntryUser::class, [
            'hiddenFilters' => ['entry' => $entry, 'shared' => true],
        ]);

        return new JsonResponse(array_merge($results, [
            'data' => array_map(function (EntryUser $entryUser) {
                return $this->serializer->serialize($entryUser->getUser(), [Options::SERIALIZE_MINIMAL]);
            }, $results['data']),
        ]));
    }

    /**
     * Shares entry ownership to users.
     *
     * @Route("/entry/{entry}/users/share", name="claro_claco_form_entry_users_share")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entryUsersShareAction(Entry $entry, Request $request): JsonResponse
    {
        $this->clacoFormManager->checkEntryShareRight($entry);

        $usersIds = $request->get('ids', false);
        if ($usersIds) {
            $this->clacoFormManager->shareEntryWithUsers($entry, $usersIds);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Unshares entry ownership from user.
     *
     * @Route("/entry/{entry}/unshare", name="claro_claco_form_entry_user_unshare")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entryUserUnshareAction(Entry $entry, Request $request): JsonResponse
    {
        $this->clacoFormManager->checkEntryShareRight($entry);

        $users = $this->decodeIdsString($request, User::class);
        foreach ($users as $user) {
            try {
                $this->clacoFormManager->switchEntryUserShared($entry, $user, false);
            } catch (\Exception $e) {
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Exports entries.
     *
     * @Route("/{clacoForm}/entries/export", name="claro_claco_form_entries_export")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     *
     * @return Response
     */
    public function clacoFormEntriesExportAction(ClacoForm $clacoForm)
    {
        $this->checkPermission('EDIT', $clacoForm->getResourceNode(), [], true);

        $content = $this->exportEntries($clacoForm);

        if ($this->clacoFormManager->hasFiles($clacoForm)) {
            $file = $this->zipEntries($content, $clacoForm);

            $response = new StreamedResponse();
            $response->setCallBack(
                function () use ($file) {
                    readfile($file);
                }
            );
            $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($clacoForm->getResourceNode()->getName().'.zip'));
            $response->headers->set('Content-Type', 'application/zip; charset=utf-8');
            $response->headers->set('Connection', 'close');
            $response->send();

            return new Response();
        } else {
            $headers = [
                'Content-Transfer-Encoding' => 'octet-stream',
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$clacoForm->getResourceNode()->getName().'.xls"',
            ];

            return new Response($content, 200, $headers);
        }
    }

    /**
     * Changes owner of an entry.
     *
     * @Route("/entry/{entry}/user/{user}/change", name="claro_claco_form_entry_user_change")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     */
    public function entryOwnerChangeAction(Entry $entry, User $user): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $entry->getClacoForm()->getResourceNode(), [], true);

        $updatedEntry = $this->clacoFormManager->changeEntryOwner($entry, $user);
        $serializedEntry = $this->serializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * Switches lock of an entry.
     *
     * @Route("/entry/{entry}/lock/switch", name="claro_claco_form_entry_lock_switch")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entryLockSwitchAction(Entry $entry): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $entry->getClacoForm()->getResourceNode(), [], true);

        $updatedEntry = $this->clacoFormManager->switchEntryLock($entry);
        $serializedEntry = $this->serializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * Switches lock of entries.
     *
     * @Route( "/entries/lock/{locked}/switch", name="claro_claco_form_entries_lock_switch")
     *
     * @param int $locked
     */
    public function entriesLockSwitchAction($locked, Request $request): JsonResponse
    {
        /** @var Entry[] $entries */
        $entries = $this->decodeIdsString($request, 'Claroline\ClacoFormBundle\Entity\Entry');
        $clacoForms = [];

        foreach ($entries as $entry) {
            $clacoForm = $entry->getClacoForm();
            $clacoFormId = $clacoForm->getId();

            if (!isset($clacoForms[$clacoFormId])) {
                $clacoForms[$clacoFormId] = $clacoForm;
            }
        }
        foreach ($clacoForms as $clacoForm) {
            $this->checkPermission('ADMINISTRATE', $clacoForm->getResourceNode(), [], true);
        }

        $updatedEntries = $this->clacoFormManager->switchEntriesLock($entries, 1 === intval($locked));
        $serializedEntries = [];

        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->serializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }

    /**
     * Downloads a file associated to a FieldValue.
     *
     * @Route("/entry/{entry}/field/{field}/file/download", name="claro_claco_form_field_value_file_download")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     *
     * @return StreamedResponse|JsonResponse
     */
    public function downloadAction(Entry $entry, string $field)
    {
        $formField = $this->om->getRepository(Field::class)->findByFieldFacetUuid($field);
        if (empty($formField) || FieldFacet::FILE_TYPE !== $formField->getType()) {
            return new JsonResponse(null, 404);
        }
        $fieldValue = $this->clacoFormManager->getFieldValueByEntryAndField($entry, $formField);
        $data = $fieldValue->getFieldFacetValue()->getValue();

        if (empty($data)) {
            return new JsonResponse(null, 404);
        }
        $response = new StreamedResponse();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.preg_replace('#^\.\.\/files\/#', '', $data['url']); // TODO : files part should not be stored in the DB

        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$data['name']);
        $response->headers->set('Content-Type', $data['mimeType']);
        $response->headers->set('Connection', 'close');

        return $response->send();
    }

    /**
     * Returns list of codes of all countries present in all entries.
     *
     * @Route("/{clacoForm}/entries/used/countries", name="claro_claco_form_used_countries_load")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function entriesUsedCountriesLoadAction(ClacoForm $clacoForm): JsonResponse
    {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $countries = $this->clacoFormManager->getAllUsedCountriesCodes($clacoForm);

        return new JsonResponse($countries, 200);
    }

    private function exportEntries(ClacoForm $clacoForm)
    {
        $entriesData = [];
        $fields = $clacoForm->getFields();
        $entries = $this->clacoFormManager->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $user = $entry->getUser();
            $publicationDate = $entry->getPublicationDate();
            $editionDate = $entry->getEditionDate();
            $fieldValues = $entry->getFieldValues();
            $data = [];
            $data['id'] = $entry->getId();
            $data['uuid'] = $entry->getUuid();
            $data['title'] = $entry->getTitle();

            $data['author'] = $this->translator->trans('anonymous', [], 'platform');
            $data['author_username'] = null;
            $data['author_email'] = null;
            if (!empty($user)) {
                $data['author'] = $user->getFirstName().' '.$user->getLastName();
                $data['author_username'] = $user->getUsername();
                $data['author_email'] = $user->getEmail();
            }

            $data['publicationDate'] = empty($publicationDate) ? '' : $publicationDate->format('d/m/Y');
            $data['editionDate'] = empty($editionDate) ? '' : $editionDate->format('d/m/Y');

            foreach ($fieldValues as $fiedValue) {
                $field = $fiedValue->getField();
                $fieldFacet = $field->getFieldFacet();
                $fieldFacetValue = $fiedValue->getFieldFacetValue();
                $val = $fieldFacetValue->getValue();

                switch ($fieldFacet->getType()) {
                    case FieldFacet::CHOICE_TYPE:
                        $value = is_array($val) ? implode(', ', $val) : $val;
                        break;
                    case FieldFacet::CASCADE_TYPE:
                        $value = is_array($val) ? implode(', ', $val) : '';
                        break;
                    case FieldFacet::COUNTRY_TYPE:
                        $value = $this->locationManager->getCountryByCode($val);
                        break;
                    case FieldFacet::FILE_TYPE:
                        if (!empty($val) && is_array($val)) {
                            $fileUrl = preg_replace('#^\.\.\/files\/#', '', $val['url']); // TODO : files part should not be stored in the DB
                            $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$fileUrl;

                            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                            $value = TextNormalizer::toKey($entry->getTitle()).'-'.TextNormalizer::toKey($field->getLabel()).'.'.$ext;
                        }
                        break;
                    default:
                        $value = $val;
                }
                $data[$field->getId()] = $value;
            }

            $entriesData[] = $data;
        }

        return $this->templating->render(
            '@ClarolineClacoForm/claco_form/entries_export.html.twig',
            [
                'fields' => $fields,
                'entries' => $entriesData,
            ]
        );
    }

    private function generatePdfForEntry(Entry $entry, User $user)
    {
        $clacoForm = $entry->getClacoForm();
        $fields = $clacoForm->getFields();
        $fieldValues = [];

        foreach ($entry->getFieldValues() as $fieldValue) {
            $field = $fieldValue->getField();
            $fieldValues[$field->getId()] = $fieldValue->getFieldFacetValue()->getValue();
        }
        $canEdit = $this->clacoFormManager->hasRight($clacoForm, 'EDIT');
        $template = $clacoForm->getTemplate();
        $useTemplate = $clacoForm->getUseTemplate();
        $displayMeta = $clacoForm->getDisplayMetadata();
        $isEntryManager = $user instanceof User && $this->clacoFormManager->isEntryManager($entry, $user);
        $withMeta = $canEdit || 'all' === $displayMeta || ('manager' === $displayMeta && $isEntryManager);
        $countries = $this->locationManager->getCountries();

        if (!empty($template) && $useTemplate) {
            $template = str_replace('%clacoform_entry_title%', $entry->getTitle(), $template);

            foreach ($fields as $field) {
                if (!$field->isHidden() && ($withMeta || !$field->isMetadata()) && isset($fieldValues[$field->getId()])) {
                    $fieldFacet = $field->getFieldFacet();

                    switch ($fieldFacet->getType()) {
                        case FieldFacet::CASCADE_TYPE:
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
                        case FieldFacet::BOOLEAN_TYPE:
                            $value = $fieldValues[$field->getId()] ? $field->getLabel() : '';
                            break;
                        default:
                            $value = $fieldValues[$field->getId()];
                    }
                } else {
                    $value = '';
                }

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $template = str_replace("%field_{$field->getUuid()}%", $value, $template);
                // for retro-compatibility with very old templates
                $template = str_replace("%field_{$field->getId()}%", $value, $template);
            }
        }
        $canViewComments = $this->clacoFormManager->canViewComments($clacoForm);
        $comments = [];

        if ($canViewComments) {
            $entryComments = $entry->getComments();

            foreach ($entryComments as $comment) {
                if (Comment::VALIDATED === $comment->getStatus()) {
                    $comments[] = $comment;
                }
            }
        }

        return $this->templating->render(
            '@ClarolineClacoForm/claco_form/entry.html.twig',
            [
                'entry' => $entry,
                'template' => $template,
                'useTemplate' => $useTemplate,
                'withMeta' => $withMeta,
                'fields' => $fields,
                'fieldValues' => $fieldValues,
                'countries' => $countries,
                'canViewComments' => $canViewComments,
                'comments' => $comments,
            ]
        );
    }

    private function zipEntries($content, ClacoForm $clacoForm)
    {
        $archive = new \ZipArchive();
        $pathArch = $this->configHandler->getParameter('tmp_dir').DIRECTORY_SEPARATOR.Uuid::uuid4()->toString().'.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $archive->addFromString($clacoForm->getResourceNode()->getName().'.xls', $content);

        $entries = $this->clacoFormManager->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $fieldValues = $entry->getFieldValues();

            foreach ($fieldValues as $fieldValue) {
                $field = $fieldValue->getField();
                $fieldFacetValue = $fieldValue->getFieldFacetValue();

                if (FieldFacet::FILE_TYPE === $field->getType()) {
                    /* TODO: change this when FILE_TYPE can accept an array of files again */
                    $file = $fieldFacetValue->getValue();

                    if (!empty($file) && is_array($file)) {
                        $fileUrl = preg_replace('#^\.\.\/files\/#', '', $file['url']); // TODO : files part should not be stored in the DB
                        $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$fileUrl;

                        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                        $fileName = TextNormalizer::toKey($entry->getTitle()).'-'.TextNormalizer::toKey($field->getLabel()).'.'.$ext;

                        $archive->addFile(
                            $filePath,
                            'files'.DIRECTORY_SEPARATOR.TextNormalizer::toKey($entry->getTitle()).DIRECTORY_SEPARATOR.$fileName
                        );
                    }
                }
            }
        }
        $archive->close();

        return $pathArch;
    }
}
