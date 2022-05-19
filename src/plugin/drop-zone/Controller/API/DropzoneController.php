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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\DropzoneTool;
use Claroline\DropZoneBundle\Event\Log\LogDocumentOpenEvent;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Entity\Team;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/dropzone", options={"expose"=true})
 */
class DropzoneController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var DropzoneManager */
    private $manager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    private $filesDir;

    /**
     * DropzoneController constructor.
     *
     * @param string $filesDir
     */
    public function __construct(FinderProvider $finder, DropzoneManager $manager, $filesDir, EventDispatcherInterface $eventDispatcher, AuthorizationCheckerInterface $authorization)
    {
        $this->finder = $finder;
        $this->manager = $manager;
        $this->filesDir = $filesDir;
        $this->eventDispatcher = $eventDispatcher;
        $this->authorization = $authorization;
    }

    /**
     * Updates a Dropzone resource.
     *
     * @Route("/{id}", name="claro_dropzone_update", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function updateAction(Dropzone $dropzone, Request $request)
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        try {
            $this->manager->update($dropzone, json_decode($request->getContent(), true));

            $closedDropStates = [
                Dropzone::STATE_FINISHED,
                Dropzone::STATE_PEER_REVIEW,
                Dropzone::STATE_WAITING_FOR_PEER_REVIEW,
            ];

            if (!$dropzone->getDropClosed() && $dropzone->getManualPlanning() && in_array($dropzone->getManualState(), $closedDropStates)) {
                $this->manager->closeAllUnfinishedDrops($dropzone);
            }

            return new JsonResponse($this->manager->serialize($dropzone));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/{id}/corrections/fetch", name="claro_dropzone_corrections_fetch", methods={"GET"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionsFetchAction(Dropzone $dropzone)
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);
        $data = $this->manager->getAllCorrectionsData($dropzone);

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/drop/{id}/correction/save", name="claro_dropzone_correction_save", methods={"POST"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionSaveAction(Drop $drop, User $user, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        /* TODO: Checks correction rights */

        try {
            $correction = $this->manager->saveCorrection(json_decode($request->getContent(), true), $user);

            return new JsonResponse(
                $this->manager->serializeCorrection($correction)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/correction/{id}/submit", name="claro_dropzone_correction_submit", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "correction",
     *     class="Claroline\DropZoneBundle\Entity\Correction",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionSubmitAction(Correction $correction, User $user)
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $this->manager->submitCorrection($correction, $user);

            return new JsonResponse(
                $this->manager->serializeCorrection($correction)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/correction/{id}/validation/switch",
     *     name="claro_dropzone_correction_validation_switch",
     *     methods={"PUT"}
     * )
     * @EXT\ParamConverter(
     *     "correction",
     *     class="Claroline\DropZoneBundle\Entity\Correction",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionValidationSwitchAction(Correction $correction, User $user)
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $this->manager->switchCorrectionValidation($correction);

            return new JsonResponse(
                $this->manager->serializeCorrection($correction)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/correction/{id}/delete", name="claro_dropzone_correction_delete", methods={"DELETE"})
     * @EXT\ParamConverter(
     *     "correction",
     *     class="Claroline\DropZoneBundle\Entity\Correction",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionDeleteAction(Correction $correction, User $user)
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $serializedCorrection = $this->manager->serializeCorrection($correction);
            $this->manager->deleteCorrection($correction);

            return new JsonResponse($serializedCorrection);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/correction/{id}/deny", name="claro_dropzone_correction_deny", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "correction",
     *     class="Claroline\DropZoneBundle\Entity\Correction",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function correctionDenyAction(Correction $correction, User $user, Request $request)
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionDenial($correction, $user, $teamId);
        $data = json_decode($request->getContent(), true);
        $comment = $data['comment'];

        try {
            $this->manager->denyCorrection($correction, $comment);

            return new JsonResponse(
                $this->manager->serializeCorrection($correction)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/{id}/peer/drop/fetch", name="claro_dropzone_peer_drop_fetch", methods={"GET"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function peerDropFetchAction(Dropzone $dropzone, User $user)
    {
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $drop = $this->manager->getPeerDrop($dropzone, $user);
        $data = empty($drop) ? null : $this->manager->serializeDrop($drop);

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}/team/{teamId}/peer/drop/fetch", name="claro_dropzone_team_peer_drop_fetch", methods={"GET"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "team",
     *     class="Claroline\TeamBundle\Entity\Team",
     *     options={"mapping": {"teamId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function teamPeerDropFetchAction(Dropzone $dropzone, Team $team, User $user)
    {
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkTeamUser($team, $user);
        $drop = $this->manager->getPeerDrop($dropzone, $user, $team->getUuid(), $team->getName());
        $data = empty($drop) ? null : $this->manager->serializeDrop($drop);

        return new JsonResponse($data);
    }

    /**
     * @Route("/tool/{tool}/document/{document}", name="claro_dropzone_tool_execute", methods={"POST"})
     * @EXT\ParamConverter(
     *     "tool",
     *     class="Claroline\DropZoneBundle\Entity\DropzoneTool",
     *     options={"mapping": {"tool": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "document",
     *     class="Claroline\DropZoneBundle\Entity\Document",
     *     options={"mapping": {"document": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
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
     * Downloads a document.
     *
     * @Route("/{document}/download", name="claro_dropzone_document_download", methods={"GET"})
     * @EXT\ParamConverter(
     *     "document",
     *     class="Claroline\DropZoneBundle\Entity\Document",
     *     options={"mapping": {"document": "uuid"}}
     * )
     *
     * @return StreamedResponse
     */
    public function downloadAction(Document $document)
    {
        $this->checkDocumentAccess($document);
        $data = $document->getData();

        $response = new StreamedResponse();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.$data['url'];
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );

        $filename = str_replace(' ', '-', $data['name'] ?? 'document');

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->headers->set('Content-Type', $data['mimeType']);
        $response->headers->set('Connection', 'close');

        $this->eventDispatcher->dispatch(new LogDocumentOpenEvent($document->getDrop()->getDropzone(), $document->getDrop(), $document), 'log');

        return $response->send();
    }

    private function checkCorrectionEdition(Correction $correction, User $user, $teamId = null)
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if ($this->authorization->isGranted('EDIT', $collection)) {
            return;
        }
        if (!$correction->isFinished()) {
            if ($correction->getUser() === $user || $correction->getTeamUuid() === $teamId) {
                return;
            }
        }

        throw new AccessDeniedException();
    }

    private function checkCorrectionDenial(Correction $correction, User $user, $teamId = null)
    {
        $drop = $correction->getDrop();
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if ($this->authorization->isGranted('EDIT', $collection)) {
            return;
        }
        if ($drop->getUser() === $user || $drop->getTeamUuid() === $teamId) {
            return;
        }

        throw new AccessDeniedException();
    }

    private function checkTeamUser(Team $team, User $user)
    {
        if (!$user->hasRole($team->getRole()->getName())) {
            throw new AccessDeniedException();
        }
    }

    private function checkDocumentAccess(Document $document)
    {
        $dropzone = $document->getDrop()->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }
    }
}
