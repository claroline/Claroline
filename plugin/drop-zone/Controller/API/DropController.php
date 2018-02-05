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

use Claroline\CoreBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\DropzoneTool;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Entity\Team;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/dropzone", options={"expose"=true})
 */
class DropController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var DropzoneManager */
    private $manager;

    /**
     * DropzoneController constructor.
     *
     * @DI\InjectParams({
     *     "finder"  = @DI\Inject("claroline.api.finder"),
     *     "manager" = @DI\Inject("claroline.manager.dropzone_manager")
     * })
     *
     * @param FinderProvider  $finder
     * @param DropzoneManager $manager
     */
    public function __construct(FinderProvider $finder, DropzoneManager $manager)
    {
        $this->finder = $finder;
        $this->manager = $manager;
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
     * @EXT\ParamConverter("team",     class="ClarolineTeamBundle:Team",         options={"mapping": {"teamId": "id"}})
     * @EXT\ParamConverter("user",     converter="current_user",                 options={"allowAnonymous"=false})
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
        $dropData = null;
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
                        $dropData = $request->request->get('dropData', false);
                        $document = $this->manager->createDocument($drop, $user, $type, $dropData);
                        $documents[] = $this->manager->serializeDocument($document);
                        break;
                }
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
        if (!in_array($user, $team->getUsers())) {
            throw new AccessDeniedException();
        }
    }
}
