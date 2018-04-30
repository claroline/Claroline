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
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\ClacoFormBundle\Serializer\CommentSerializer;
use Claroline\ClacoFormBundle\Serializer\EntrySerializer;
use Claroline\ClacoFormBundle\Serializer\EntryUserSerializer;
use Claroline\ClacoFormBundle\Serializer\FieldSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClacoFormController extends Controller
{
    private $apiManager;
    private $clacoFormManager;
    private $filesDir;
    private $finder;
    private $platformConfigHandler;
    private $request;
    private $roleManager;
    private $roleSerializer;

    /** @var SerializerProvider */
    private $serializer;
    private $tokenStorage;
    private $userManager;
    private $entrySerializer;
    private $commentSerializer;
    private $fieldSerializer;
    private $entryUserSerializer;

    /**
     * @DI\InjectParams({
     *     "apiManager"            = @DI\Inject("claroline.manager.api_manager"),
     *     "clacoFormManager"      = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "filesDir"              = @DI\Inject("%claroline.param.files_directory%"),
     *     "finder"                = @DI\Inject("claroline.api.finder"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "request"               = @DI\Inject("request"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "roleSerializer"        = @DI\Inject("claroline.serializer.role"),
     *     "serializer"            = @DI\Inject("claroline.api.serializer"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *     "entrySerializer"       = @DI\Inject("claroline.serializer.clacoform.entry"),
     *     "commentSerializer"     = @DI\Inject("claroline.serializer.clacoform.comment"),
     *     "fieldSerializer"       = @DI\Inject("claroline.serializer.clacoform.field"),
     *     "entryUserSerializer"   = @DI\Inject("claroline.serializer.clacoform.entry.user")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        ClacoFormManager $clacoFormManager,
        $filesDir,
        FinderProvider $finder,
        PlatformConfigurationHandler $platformConfigHandler,
        Request $request,
        RoleManager $roleManager,
        RoleSerializer $roleSerializer,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        EntrySerializer $entrySerializer,
        CommentSerializer $commentSerializer,
        FieldSerializer $fieldSerializer,
        EntryUserSerializer $entryUserSerializer
    ) {
        $this->apiManager = $apiManager;
        $this->clacoFormManager = $clacoFormManager;
        $this->filesDir = $filesDir;
        $this->finder = $finder;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $request;
        $this->roleManager = $roleManager;
        $this->roleSerializer = $roleSerializer;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->entrySerializer = $entrySerializer;
        $this->commentSerializer = $commentSerializer;
        $this->fieldSerializer = $fieldSerializer;
        $this->entryUserSerializer = $entryUserSerializer;
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/open",
     *     name="claro_claco_form_open",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function clacoFormOpenAction(ClacoForm $clacoForm)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'OPEN');
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = 'anon.' === $user;
        $myEntries = $isAnon ? [] : $this->clacoFormManager->getUserEntries($clacoForm, $user);
        $canGeneratePdf = !$isAnon &&
            $this->platformConfigHandler->hasParameter('knp_pdf_binary_path') &&
            file_exists($this->platformConfigHandler->getParameter('knp_pdf_binary_path'));
        $cascadeLevelMax = $this->platformConfigHandler->hasParameter('claco_form_cascade_select_level_max') ?
            $this->platformConfigHandler->getParameter('claco_form_cascade_select_level_max') :
            2;
        $roles = [];
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $roleAnonymous = $this->roleManager->getRoleByName('ROLE_ANONYMOUS');
        $workspaceRoles = $this->roleManager->getWorkspaceRoles($clacoForm->getResourceNode()->getWorkspace());
        $roles[] = $this->roleSerializer->serialize($roleUser, [Options::SERIALIZE_MINIMAL]);
        $roles[] = $this->roleSerializer->serialize($roleAnonymous, [Options::SERIALIZE_MINIMAL]);

        foreach ($workspaceRoles as $workspaceRole) {
            $roles[] = $this->roleSerializer->serialize($workspaceRole, [Options::SERIALIZE_MINIMAL]);
        }
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $myRoles = 'anon.' === $currentUser ? [$roleAnonymous->getName()] : $currentUser->getRoles();

        return [
            '_resource' => $clacoForm,
            'clacoForm' => $clacoForm,
            'canGeneratePdf' => $canGeneratePdf,
            'cascadeLevelMax' => $cascadeLevelMax,
            'myEntriesCount' => count($myEntries),
            'roles' => $roles,
            'myRoles' => $myRoles,
        ];
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/template/edit",
     *     name="claro_claco_form_template_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * @param ClacoForm $clacoForm
     *
     * @return JsonResponse
     */
    public function clacoFormTemplateEditAction(ClacoForm $clacoForm)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'EDIT');
        $template = $this->request->request->get('template', false);
        $useTemplate = $this->request->request->get('useTemplate', false);
        $useTemplate = $useTemplate && 1 === intval($useTemplate);
        $clacoFormTemplate = $this->clacoFormManager->saveClacoFormTemplate($clacoForm, $template, $useTemplate);

        return new JsonResponse(['template' => $clacoFormTemplate, 'useTemplate' => $clacoForm->getUseTemplate()], 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/keyword/get/by/name/{name}/excluding/uuid/{uuid}",
     *     name="claro_claco_form_get_keyword_by_name_excluding_uuid",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * Returns the keyword
     *
     * @param ClacoForm $clacoForm
     * @param string    $name
     * @param string    $uuid
     *
     * @return JsonResponse
     */
    public function getKeywordByNameExcludingUuidAction(ClacoForm $clacoForm, $name, $uuid)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'EDIT');
        $keyword = $this->clacoFormManager->getKeywordByNameExcludingUuid($clacoForm, $name, $uuid);
        $serializedKeyword = !empty($keyword) ? $this->serializer->serialize($keyword) : null;

        return new JsonResponse($serializedKeyword, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/entry/random",
     *     name="claro_claco_form_entry_random",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * Returns id of a random entry
     *
     * @param ClacoForm $clacoForm
     *
     * @return JsonResponse
     */
    public function entryRandomAction(ClacoForm $clacoForm)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'OPEN');
        $entryId = $this->clacoFormManager->getRandomEntryId($clacoForm);

        return new JsonResponse($entryId, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/entry/create",
     *     name="claro_claco_form_entry_create",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * Creates an entry
     *
     * @param ClacoForm $clacoForm
     *
     * @return JsonResponse
     */
    public function entryCreateAction(ClacoForm $clacoForm)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'OPEN');
        $user = $this->tokenStorage->getToken()->getUser();
        $entryUser = 'anon.' === $user ? null : $user;
        $entryData = $this->request->request->get('entryData', false);
        $title = $this->request->request->get('titleData', false);
        $keywordsData = $this->request->request->get('keywordsData', false);
        $files = $this->request->files->all();

        if (!is_array($entryData)) {
            $entryData = json_decode($entryData, true);
        }
        if (!is_array($keywordsData)) {
            $keywordsData = json_decode($keywordsData, true);
        }
        if (!$title) {
            $title = $entryData['entry_title'];
        }

        if ($this->clacoFormManager->canCreateEntry($clacoForm, $entryUser)) {
            $entry = $this->clacoFormManager->createEntry($clacoForm, $entryData, $title, $keywordsData, $entryUser, $files);
        } else {
            $entry = null;
        }
        $serializedEntry = $this->entrySerializer->serialize($entry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/edit",
     *     name="claro_claco_form_entry_edit",
     *     options = {"expose"=true}
     * )
     *
     * Edits entry
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function entryEditAction(Entry $entry)
    {
        $this->clacoFormManager->checkEntryEdition($entry);
        $entryData = $this->request->request->get('entryData', false);
        $title = $this->request->request->get('titleData', false);
        $categoriesIds = $this->request->request->get('categoriesData', false);
        $keywordsData = $this->request->request->get('keywordsData', false);
        $files = $this->request->files->all();

        if (!is_array($entryData)) {
            $entryData = json_decode($entryData, true);
        }
        if (!is_array($keywordsData)) {
            $keywordsData = json_decode($keywordsData, true);
        }
        if (!is_array($categoriesIds)) {
            $categoriesIds = json_decode($categoriesIds, true);
        }
        if (!$title) {
            $title = $entryData['entry_title'];
        }
        $updatedEntry = $this->clacoFormManager->editEntry($entry, $entryData, $title, $categoriesIds, $keywordsData, $files);
        $serializedEntry = $this->entrySerializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entries/delete",
     *     name="claro_claco_form_entries_delete",
     *     options = {"expose"=true}
     * )
     *
     * Deletes entries
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function entriesDeleteAction()
    {
        $entries = [];
        $serializedEntries = [];
        $entriesParams = $this->apiManager->getParametersByUuid('ids', 'Claroline\ClacoFormBundle\Entity\Entry');

        foreach ($entriesParams as $entryParam) {
            if (!$entryParam->isLocked()) {
                $entries[] = $entryParam;
            }
        }
        foreach ($entries as $entry) {
            $this->clacoFormManager->checkEntryEdition($entry);
            $serializedEntries[] = $this->entrySerializer->serialize($entry);
        }
        $this->clacoFormManager->deleteEntries($entries);

        return new JsonResponse($serializedEntries, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/status/change",
     *     name="claro_claco_form_entry_status_change",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * Changes status of an entry
     *
     * @param Entry $entry
     *
     * @return JsonResponse
     */
    public function entryStatusChangeAction(Entry $entry)
    {
        if ($entry->isLocked()) {
            $serializedEntry = $this->entrySerializer->serialize($entry);
        } else {
            $this->clacoFormManager->checkEntryModeration($entry);
            $updatedEntry = $this->clacoFormManager->changeEntryStatus($entry);
            $serializedEntry = $this->entrySerializer->serialize($updatedEntry);
        }

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entries/status/{status}/change",
     *     name="claro_claco_form_entries_status_change",
     *     options = {"expose"=true}
     * )
     *
     * Changes status of entries
     *
     * @param int $status
     *
     * @return JsonResponse
     */
    public function entriesStatusChangeAction($status)
    {
        $entries = [];
        $serializedEntries = [];
        $entriesParams = $this->apiManager->getParametersByUuid('ids', 'Claroline\ClacoFormBundle\Entity\Entry');

        foreach ($entriesParams as $entryParam) {
            if (!$entryParam->isLocked()) {
                $entries[] = $entryParam;
            }
        }
        foreach ($entries as $entry) {
            $this->clacoFormManager->checkEntryModeration($entry);
        }
        $updatedEntries = $this->clacoFormManager->changeEntriesStatus($entries, intval($status));

        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->entrySerializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/comments/retrieve",
     *     name="claro_claco_form_entry_comments_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * Retrieves comments of an entry
     *
     * @param Entry $entry
     *
     * @return JsonResponse
     */
    public function entryCommentsRetrieveAction(Entry $entry)
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
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
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/comment/create",
     *     name="claro_claco_form_entry_comment_create",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * Creates a comment
     *
     * @param Entry $entry
     *
     * @return JsonResponse
     */
    public function commentCreateAction(Entry $entry)
    {
        $this->clacoFormManager->checkCommentCreationRight($entry);
        $content = $this->request->request->get('commentData', false);
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $user = 'anon.' !== $authenticatedUser ? $authenticatedUser : null;
        $comment = $this->clacoFormManager->createComment($entry, $content, $user);
        $serializedComment = $this->commentSerializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/comment/{comment}/edit",
     *     name="claro_claco_form_entry_comment_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "comment",
     *     class="ClarolineClacoFormBundle:Comment",
     *     options={"mapping": {"comment": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Edits a comment
     *
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function commentEditAction(Comment $comment)
    {
        $this->clacoFormManager->checkCommentEditionRight($comment);
        $content = $this->request->request->get('commentData', false);
        $comment = $this->clacoFormManager->editComment($comment, $content);
        $serializedComment = $this->commentSerializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/comment/{comment}/delete",
     *     name="claro_claco_form_entry_comment_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "comment",
     *     class="ClarolineClacoFormBundle:Comment",
     *     options={"mapping": {"comment": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes a comment
     *
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function commentDeleteAction(Comment $comment)
    {
        $this->clacoFormManager->checkCommentEditionRight($comment);
        $this->clacoFormManager->deleteComment($comment);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/comment/{comment}/activate",
     *     name="claro_claco_form_entry_comment_activate",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "comment",
     *     class="ClarolineClacoFormBundle:Comment",
     *     options={"mapping": {"comment": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Activates a comment
     *
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function commentActivateAction(Comment $comment)
    {
        $this->clacoFormManager->checkEntryModeration($comment->getEntry());
        $comment = $this->clacoFormManager->changeCommentStatus($comment, Comment::VALIDATED);
        $serializedComment = $this->serializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/comment/{comment}/block",
     *     name="claro_claco_form_entry_comment_block",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "comment",
     *     class="ClarolineClacoFormBundle:Comment",
     *     options={"mapping": {"comment": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Blocks a comment
     *
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function commentBlockAction(Comment $comment)
    {
        $this->clacoFormManager->checkEntryModeration($comment->getEntry());
        $comment = $this->clacoFormManager->changeCommentStatus($comment, Comment::BLOCKED);
        $serializedComment = $this->serializer->serialize($comment);

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/user/retrieve",
     *     name="claro_claco_form_entry_user_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves an entry options for current user
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function entryUserRetrieveAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $entryUser = $this->clacoFormManager->getEntryUser($entry, $user);
        $serializedEntryUser = $this->entryUserSerializer->serialize($entryUser);

        return new JsonResponse($serializedEntryUser, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/user/save",
     *     name="claro_claco_form_entry_user_save",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Saves entry options for current user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function entryUserSaveAction(User $user, Entry $entry)
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $entryUser = $this->clacoFormManager->getEntryUser($entry, $user);
        $entryUserData = $this->request->request->get('entryUserData', false);

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

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/pdf/download",
     *     name="claro_claco_form_entry_pdf_download",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Downloads pdf version of entry
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return Response
     */
    public function entryPdfDownloadAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkEntryAccess($entry);
        $pdf = $this->clacoFormManager->generatePdfForEntry($entry, $user);

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$entry->getTitle().'.pdf"',
        ];

        return new Response(
            file_get_contents($this->filesDir.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.$pdf->getPath()),
            200,
            $headers
        );
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entries/pdf/download",
     *     name="claro_claco_form_entries_pdf_download",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Downloads pdf version of entries into a ZIP archive
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entriesPdfDownloadAction(User $user)
    {
        $entries = $this->apiManager->getParametersByUuid('ids', 'Claroline\ClacoFormBundle\Entity\Entry');
        $fileName = count($entries) > 0 ? $entries[0]->getClacoForm()->getResourceNode()->getName() : 'clacoForm';

        foreach ($entries as $entry) {
            $this->clacoFormManager->checkEntryAccess($entry);
        }

        $archive = $this->clacoFormManager->generateArchiveForEntries($entries, $user);

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($archive) {
                readfile($archive);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($fileName.'.zip'));
        $response->headers->set('Content-Type', 'application/zip; charset=utf-8');
        $response->headers->set('Connection', 'close');
        $response->send();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/shared/users/list",
     *     name="claro_claco_form_entry_shared_users_list",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves list of users the entry is shared with
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function entrySharedUsersListAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkEntryShareRight($entry);
        $users = $this->clacoFormManager->getSharedEntryUsers($entry);
        $serializedUsers = array_map(function (User $user) {
            return $this->serializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
        }, $users);
        $whitelist = $this->userManager->getAllVisibleUsersIdsForUserPicker($user);

        return new JsonResponse(['users' => $serializedUsers, 'whitelist' => $whitelist], 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/users/share",
     *     name="claro_claco_form_entry_users_share",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * Shares entry ownership to users
     *
     * @param Entry $entry
     *
     * @return JsonResponse
     */
    public function entryUsersShareAction(Entry $entry)
    {
        $this->clacoFormManager->checkEntryShareRight($entry);
        $usersIds = $this->request->request->get('usersIds', false);

        if ($usersIds) {
            $this->clacoFormManager->shareEntryWithUsers($entry, $usersIds);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/user/{user}/share",
     *     name="claro_claco_form_entry_user_share",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "user",
     *     class="ClarolineCoreBundle:User",
     *     options={"mapping": {"user": "uuid"}}
     * )
     *
     * Shares entry ownership to user
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function entryUserShareAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkEntryShareRight($entry);
        $this->clacoFormManager->switchEntryUserShared($entry, $user, true);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/user/{user}/unshare",
     *     name="claro_claco_form_entry_user_unshare",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "user",
     *     class="ClarolineCoreBundle:User",
     *     options={"mapping": {"user": "uuid"}}
     * )
     *
     * Unshares entry ownership from user
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function entryUserUnshareAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkEntryShareRight($entry);
        $this->clacoFormManager->switchEntryUserShared($entry, $user, false);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/{clacoForm}/entries/export",
     *     name="claro_claco_form_entries_export",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * Exports entries
     *
     * @param ClacoForm $clacoForm
     *
     * @return Response
     */
    public function clacoFormEntriesExportAction(ClacoForm $clacoForm)
    {
        $this->clacoFormManager->checkRight($clacoForm, 'EDIT');
        $content = $this->clacoFormManager->exportEntries($clacoForm);

        if ($this->clacoFormManager->hasFiles($clacoForm)) {
            $file = $this->clacoFormManager->zipEntries($content, $clacoForm);

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
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/user/{user}/change",
     *     name="claro_claco_form_entry_user_change",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "user",
     *     class="ClarolineCoreBundle:User",
     *     options={"mapping": {"user": "uuid"}}
     * )
     *
     * Changes owner of an entry
     *
     * @param Entry $entry
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function entryOwnerChangeAction(Entry $entry, User $user)
    {
        $this->clacoFormManager->checkRight($entry->getClacoForm(), 'ADMINISTRATE');
        $updatedEntry = $this->clacoFormManager->changeEntryOwner($entry, $user);
        $serializedEntry = $this->serializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/lock/switch",
     *     name="claro_claco_form_entry_lock_switch",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * Switches lock of an entry
     *
     * @param Entry $entry
     *
     * @return JsonResponse
     */
    public function entryLockSwitchAction(Entry $entry)
    {
        $this->clacoFormManager->checkRight($entry->getClacoForm(), 'ADMINISTRATE');
        $updatedEntry = $this->clacoFormManager->switchEntryLock($entry);
        $serializedEntry = $this->entrySerializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entries/lock/{locked}/switch",
     *     name="claro_claco_form_entries_lock_switch",
     *     options = {"expose"=true}
     * )
     *
     * Switches lock of entries
     *
     * @param int $locked
     *
     * @return JsonResponse
     */
    public function entriesLockSwitchAction($locked)
    {
        $entries = $this->apiManager->getParametersByUuid('ids', 'Claroline\ClacoFormBundle\Entity\Entry');
        $clacoForms = [];

        foreach ($entries as $entry) {
            $clacoForm = $entry->getClacoForm();
            $clacoFormId = $clacoForm->getId();

            if (!isset($clacoForms[$clacoFormId])) {
                $clacoForms[$clacoFormId] = $clacoForm;
            }
        }
        foreach ($clacoForms as $clacoForm) {
            $this->clacoFormManager->checkRight($clacoForm, 'ADMINISTRATE');
        }

        $updatedEntries = $this->clacoFormManager->switchEntriesLock($entries, 1 === intval($locked));
        $serializedEntries = [];

        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->entrySerializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/entry/{entry}/field/{field}/file/download",
     *     name="claro_claco_form_field_value_file_download",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "field",
     *     class="ClarolineClacoFormBundle:Field",
     *     options={"mapping": {"field": "uuid"}}
     * )
     *
     * Downloads a file associated to a FieldValue.
     *
     * @param Entry $entry
     * @param Field $field
     *
     * @return StreamedResponse
     */
    public function downloadAction(Entry $entry, Field $field)
    {
        if ($field->getType() !== FieldFacet::FILE_TYPE) {
            return new JsonResponse(null, 404);
        }
        $fieldValue = $this->clacoFormManager->getFieldValueByEntryAndField($entry, $field);
        $data = $fieldValue->getFieldFacetValue()->getValue();

        if (empty($data)) {
            return new JsonResponse(null, 404);
        }
        $response = new StreamedResponse();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.$data['url'];
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
}
