<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\DropzoneTool;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Entity\Team;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/dropzone", options={"expose"=true})
 */
class DropController
{
    use PermissionCheckerTrait;

    /** @var ApiManager */
    private $apiManager;

    /** @var FinderProvider */
    private $finder;

    /** @var DropzoneManager */
    private $manager;

    /**
     * DropController constructor.
     *
     * @DI\InjectParams({
     *     "apiManager" = @DI\Inject("claroline.manager.api_manager"),
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "manager"    = @DI\Inject("claroline.manager.dropzone_manager"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ApiManager      $apiManager
     * @param FinderProvider  $finder
     * @param DropzoneManager $manager
     */
    public function __construct(
        ApiManager $apiManager,
        FinderProvider $finder,
        DropzoneManager $manager,
        ObjectManager $om
    ) {
        $this->apiManager = $apiManager;
        $this->finder = $finder;
        $this->manager = $manager;
        $this->om = $om;
    }

    /**
     * @EXT\Route("/drop/{id}", name="claro_dropzone_drop_fetch")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop $drop
     *
     * @return JsonResponse
     */
    public function getAction(Drop $drop)
    {
        $dropzone = $drop->getDropzone();
        /* TODO: checks if current user can edit resource or view this drop */
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);

        return new JsonResponse($this->manager->serializeDrop($drop));
    }

    /**
     * @EXT\Route("/{id}/drops/search", name="claro_dropzone_drops_search")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="ClarolineDropZoneBundle:Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Dropzone $dropzone
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function listAction(Dropzone $dropzone, Request $request)
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $data = $this->finder->search('Claroline\DropZoneBundle\Entity\Drop', array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['dropzone' => $dropzone->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * Initializes a Drop for the current User or Team.
     *
     * @EXT\Route("/{id}/drops/{teamId}", name="claro_dropzone_drop_create", defaults={"teamId"=null})
     * @EXT\ParamConverter("dropzone", class="ClarolineDropZoneBundle:Dropzone", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("team", class="ClarolineTeamBundle:Team", options={"mapping": {"teamId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("POST")
     *
     * @param Dropzone $dropzone
     * @param Team     $team
     * @param User     $user
     *
     * @return JsonResponse
     */
    public function createAction(Dropzone $dropzone, User $user, Team $team = null)
    {
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);

        if (!empty($team)) {
            $this->checkTeamUser($team, $user);
        }
        try {
            if (empty($team)) {
                // creates a User drop
                $myDrop = $this->manager->getUserDrop($dropzone, $user, true);
            } else {
                // creates a Team drop
                $myDrop = $this->manager->getTeamDrop($dropzone, $team, $user, true);
            }

            return new JsonResponse($this->manager->serializeDrop($myDrop));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Submits Drop.
     *
     * @EXT\Route("/drop/{id}/submit", name="claro_dropzone_drop_submit")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop $drop
     * @param User $user
     *
     * @return JsonResponse
     */
    public function submitAction(Drop $drop, User $user)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkDropEdition($drop, $user);

        try {
            $this->manager->submitDrop($drop, $user);
            $progression = $dropzone->isPeerReview() ? 50 : 100;
            $this->manager->updateDropProgression($dropzone, $drop, $progression);

            return new JsonResponse($this->manager->serializeDrop($drop));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Cancels Drop submission.
     *
     * @EXT\Route("/drop/{id}/submission/cancel", name="claro_dropzone_drop_submission_cancel")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop $drop
     *
     * @return JsonResponse
     */
    public function cancelAction(Drop $drop)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $this->manager->cancelDropSubmission($drop);

            return new JsonResponse($this->manager->serializeDrop($drop));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Adds a Document to a Drop.
     *
     * @EXT\Route("/drop/{id}/type/{type}", name="claro_dropzone_documents_add")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop    $drop
     * @param int     $type
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addDocumentAction(Drop $drop, $type, User $user, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkDropEdition($drop, $user);
        $documents = [];

        try {
            if (!$drop->isFinished()) {
                switch ($type) {
                    case Document::DOCUMENT_TYPE_FILE:
                        $files = $request->files->all();
                        $documents = $this->manager->createFilesDocuments($drop, $user, $files);
                        break;
                    case Document::DOCUMENT_TYPE_TEXT:
                    case Document::DOCUMENT_TYPE_URL:
                    case Document::DOCUMENT_TYPE_RESOURCE:
                        $data = $request->request->get('dropData');
                        if ($data) {
                            $data = json_decode($data, true);
                        }
                        $document = $this->manager->createDocument($drop, $user, $type, $data);
                        $documents[] = $this->manager->serializeDocument($document);
                        break;
                }
                $progression = $dropzone->isPeerReview() ? 0 : 50;
                $this->manager->updateDropProgression($dropzone, $drop, $progression);
            }

            return new JsonResponse($documents);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Deletes a Document.
     *
     * @EXT\Route("/document/{id}", name="claro_dropzone_document_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "document",
     *     class="ClarolineDropZoneBundle:Document",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Document $document
     * @param User     $user
     *
     * @return JsonResponse
     */
    public function deleteDocumentAction(Document $document, User $user)
    {
        $drop = $document->getDrop();
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkDropEdition($drop, $user);

        try {
            $documentId = $document->getUuid();
            $this->manager->deleteDocument($document);

            return new JsonResponse($documentId);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Adds a manager Document to a Drop.
     *
     * @EXT\Route(
     *     "/drop/{id}/revision/{revision}/type/{type}/manager",
     *     name="claro_dropzone_manager_documents_add"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "revision",
     *     class="ClarolineDropZoneBundle:Revision",
     *     options={"mapping": {"revision": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop     $drop
     * @param Revision $revision
     * @param int      $type
     * @param User     $user
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function addManagerDocumentAction(Drop $drop, Revision $revision, $type, User $user, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);
        $documents = [];

        try {
            if (!$drop->isFinished()) {
                switch ($type) {
                    case Document::DOCUMENT_TYPE_FILE:
                        $files = $request->files->all();
                        $documents = $this->manager->createFilesDocuments($drop, $user, $files, $revision, true);
                        break;
                    case Document::DOCUMENT_TYPE_TEXT:
                    case Document::DOCUMENT_TYPE_URL:
                    case Document::DOCUMENT_TYPE_RESOURCE:
                        $uuid = $request->request->get('dropData', false);
                        $document = $this->manager->createDocument($drop, $user, $type, $uuid, $revision, true);
                        $documents[] = $this->manager->serializeDocument($document);
                        break;
                }
                $progression = $dropzone->isPeerReview() ? 0 : 50;
                $this->manager->updateDropProgression($dropzone, $drop, $progression);
            }

            return new JsonResponse($documents);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Deletes a manager Document.
     *
     * @EXT\Route("/document/{id}/manager", name="claro_dropzone_manager_document_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "document",
     *     class="ClarolineDropZoneBundle:Document",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Document $document
     *
     * @return JsonResponse
     */
    public function deleteManagerDocumentAction(Document $document)
    {
        $drop = $document->getDrop();
        $dropzone = $drop->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $documentId = $document->getUuid();
            $this->manager->deleteDocument($document);

            return new JsonResponse($documentId);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Unlocks Drop.
     *
     * @EXT\Route("/drop/{id}/unlock", name="claro_dropzone_drop_unlock")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop $drop
     *
     * @return JsonResponse
     */
    public function dropUnlockAction(Drop $drop)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $this->manager->unlockDrop($drop);

            return new JsonResponse($this->manager->serializeDrop($drop));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Unlocks Drop user.
     *
     * @EXT\Route("/drop/{id}/unlock/user", name="claro_dropzone_drop_unlock_user")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop $drop
     *
     * @return JsonResponse
     */
    public function dropUserUnlockAction(Drop $drop)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $this->manager->unlockDropUser($drop);

            return new JsonResponse($this->manager->serializeDrop($drop));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @EXT\Route("/tool/{tool}/document/{document}", name="claro_dropzone_tool_execute")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "tool",
     *     class="ClarolineDropZoneBundle:DropzoneTool",
     *     options={"mapping": {"tool": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "document",
     *     class="ClarolineDropZoneBundle:Document",
     *     options={"mapping": {"document": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param DropzoneTool $tool
     * @param Document     $document
     *
     * @return JsonResponse
     */
    public function toolExecuteAction(DropzoneTool $tool, Document $document)
    {
        $dropzone = $document->getDrop()->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $updatedDocument = $this->manager->executeTool($tool, $document);

            return new JsonResponse($this->manager->serializeDocument($updatedDocument));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @EXT\Route("/drops/download", name="claro_dropzone_drops_download")
     * @EXT\Method("POST")
     *
     * Downloads drops documents into a ZIP archive
     *
     * @return StreamedResponse
     */
    public function dropsDownloadAction(Request $request)
    {
        $drops = $this->decodeIdsString($request, Drop::class);
        $dropzone = $drops[0]->getDropzone();
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);
        $fileName = $dropzone->getResourceNode()->getName();

        $archive = $this->manager->generateArchiveForDrops($drops);

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

        return $response->send();
    }

    /**
     * @EXT\Route(
     *     "/drop/{id}/next",
     *     name="claro_dropzone_drop_next"
     * )
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Drop    $drop
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function nextDropAction(Drop $drop, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if (!$this->authorization->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException();
        }
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Drop[] $data */
        $data = $this->finder->get(Drop::class)->find($filters, $sortBy, 0, -1, false/*, [Options::SQL_ARRAY_MAP]*/);
        $next = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $drop->getUuid()) {
                $next = $position + 1;
            }
        }

        $nextDrop = array_key_exists($next, $data) ? $data[$next] : reset($data);

        return new JsonResponse($this->manager->serializeDrop($nextDrop), 200);
    }

    /**
     * @EXT\Route(
     *     "/drop/{id}/previous",
     *     name="claro_dropzone_drop_previous"
     * )
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Drop    $drop
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function previousDropAction(Drop $drop, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if (!$this->authorization->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException();
        }
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Drop[] $data */
        $data = $this->finder->get(Drop::class)->find($filters, $sortBy, 0, -1, false/*, [Options::SQL_ARRAY_MAP]*/);
        $previous = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $drop->getUuid()) {
                $previous = $position - 1;
            }
        }

        $previousDrop = array_key_exists($previous, $data) ? $data[$previous] : end($data);

        return new JsonResponse($this->manager->serializeDrop($previousDrop), 200);
    }

    private function checkDropEdition(Drop $drop, User $user)
    {
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if ($this->authorization->isGranted('EDIT', $collection)) {
            return;
        }
        if ($dropzone->isDropEnabled()) {
            if ($drop->getUser() === $user || in_array($user, $drop->getUsers())) {
                return;
            }
        }

        throw new AccessDeniedException();
    }

    private function checkTeamUser(Team $team, User $user)
    {
        if (!$user->hasRole($team->getRole()->getName())) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return array
     */
    protected function decodeIdsString(Request $request, $class)
    {
        $ids = json_decode($request->getContent(), true)['_ids'];
        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }
}
