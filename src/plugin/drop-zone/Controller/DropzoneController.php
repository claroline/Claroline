<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Exception;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\CorrectionManager;
use Claroline\DropZoneBundle\Manager\DropManager;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/dropzone', options: ['expose' => true])]
class DropzoneController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly Crud $crud,
        private readonly DropzoneManager $manager,
        private readonly string $filesDir,
        AuthorizationCheckerInterface $authorization,
        private readonly SerializerProvider $serializer,
        private readonly DropManager $dropManager,
        private readonly CorrectionManager $correctionManager
    ) {
        $this->authorization = $authorization;
    }

    
    #[Route(path: '/{id}/corrections/fetch', name: 'claro_dropzone_corrections_fetch', methods: ['GET'])]
    public function correctionsFetchAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Dropzone', mapping: ['id' => 'uuid'])]
    Dropzone $dropzone): JsonResponse
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);
        $data = $this->correctionManager->getAllCorrectionsData($dropzone);

        return new JsonResponse($data, 200);
    }

    
    #[Route(path: '/drop/{id}/correction/save', name: 'claro_dropzone_correction_save', methods: ['POST'])]
    public function correctionSaveAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Drop', mapping: ['id' => 'uuid'])]
    Drop $drop, #[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        /* TODO: Checks correction rights */

        try {
            $correction = $this->correctionManager->saveCorrection(json_decode($request->getContent(), true), $user);

            return new JsonResponse(
                $this->serializer->serialize($correction)
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    
    #[Route(path: '/correction/{id}/submit', name: 'claro_dropzone_correction_submit', methods: ['PUT'])]
    public function correctionSubmitAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Correction', mapping: ['id' => 'uuid'])]
    Correction $correction, #[CurrentUser] ?User $user): JsonResponse
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $this->correctionManager->submitCorrection($correction, $user);

            return new JsonResponse(
                $this->serializer->serialize($correction)
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    
    #[Route(path: '/correction/{id}/validation/switch', name: 'claro_dropzone_correction_validation_switch', methods: ['PUT'])]
    public function correctionValidationSwitchAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Correction', mapping: ['id' => 'uuid'])]
    Correction $correction, #[CurrentUser] ?User $user): JsonResponse
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $this->correctionManager->switchCorrectionValidation($correction);

            return new JsonResponse(
                $this->serializer->serialize($correction)
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    
    #[Route(path: '/correction/{id}/delete', name: 'claro_dropzone_correction_delete', methods: ['DELETE'])]
    public function correctionDeleteAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Correction', mapping: ['id' => 'uuid'])]
    Correction $correction, #[CurrentUser] ?User $user): JsonResponse
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionEdition($correction, $user, $teamId);

        try {
            $serializedCorrection = $this->serializer->serialize($correction);
            $this->correctionManager->deleteCorrection($correction);

            return new JsonResponse($serializedCorrection);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    
    #[Route(path: '/correction/{id}/deny', name: 'claro_dropzone_correction_deny', methods: ['PUT'])]
    public function correctionDenyAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Correction', mapping: ['id' => 'uuid'])]
    Correction $correction, #[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $dropzone = $correction->getDrop()->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $teamId = $this->manager->getUserTeamId($dropzone, $user);
        $this->checkCorrectionDenial($correction, $user, $teamId);
        $data = json_decode($request->getContent(), true);
        $comment = $data['comment'];

        try {
            $this->correctionManager->denyCorrection($correction, $comment);

            return new JsonResponse(
                $this->serializer->serialize($correction)
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    
    #[Route(path: '/{id}/peer/drop/fetch', name: 'claro_dropzone_peer_drop_fetch', methods: ['GET'])]
    public function peerDropFetchAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Dropzone', mapping: ['id' => 'uuid'])]
    Dropzone $dropzone, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $drop = $this->dropManager->getPeerDrop($dropzone, $user);
        $data = empty($drop) ? null : $this->serializer->serialize($drop);

        return new JsonResponse($data);
    }

    
    #[Route(path: '/{id}/team/{teamId}/peer/drop/fetch', name: 'claro_dropzone_team_peer_drop_fetch', methods: ['GET'])]
    public function teamPeerDropFetchAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Dropzone', mapping: ['id' => 'uuid'])]
    Dropzone $dropzone, #[MapEntity(class: 'Claroline\CommunityBundle\Entity\Team', mapping: ['teamId' => 'uuid'])]
    Team $team, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkTeamUser($team, $user);
        $drop = $this->dropManager->getPeerDrop($dropzone, $user, $team->getUuid(), $team->getName());
        $data = empty($drop) ? null : $this->serializer->serialize($drop);

        return new JsonResponse($data);
    }

    /**
     * Downloads a document.
     *
     */
    #[Route(path: '/{document}/download', name: 'claro_dropzone_document_download', methods: ['GET'])]
    public function downloadAction(#[MapEntity(class: 'Claroline\DropZoneBundle\Entity\Document', mapping: ['document' => 'uuid'])]
    Document $document): StreamedResponse
    {
        $this->checkDocumentAccess($document);
        $data = $document->getData();

        $response = new StreamedResponse();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.$data['url'];
        $response->setCallBack(
            function () use ($path): void {
                readfile($path);
            }
        );

        $filename = str_replace(' ', '-', $data['name'] ?? 'document');

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->headers->set('Content-Type', $data['mimeType']);
        $response->headers->set('Connection', 'close');

        return $response->send();
    }

    private function checkCorrectionEdition(Correction $correction, User $user, $teamId = null): void
    {
        // TODO : move this in the CorrectionVoter
        $dropzone = $correction->getDrop()->getDropzone();

        if ($this->checkPermission('EDIT', $dropzone->getResourceNode())) {
            return;
        }

        if (!$correction->isFinished()) {
            if ($correction->getUser() === $user || $correction->getTeamUuid() === $teamId) {
                return;
            }
        }

        throw new AccessDeniedException();
    }

    private function checkCorrectionDenial(Correction $correction, User $user, $teamId = null): void
    {
        // TODO : move this in the voter
        $drop = $correction->getDrop();
        $dropzone = $drop->getDropzone();

        if ($this->checkPermission('EDIT', $dropzone->getResourceNode())) {
            return;
        }

        if ($drop->getUser() === $user || $drop->getTeamUuid() === $teamId) {
            return;
        }

        throw new AccessDeniedException();
    }

    private function checkTeamUser(Team $team, User $user): void
    {
        // TODO : move this in the voter
        if (!$user->hasRole($team->getRole()->getName())) {
            throw new AccessDeniedException();
        }
    }

    private function checkDocumentAccess(Document $document): void
    {
        // TODO : move this in the voter
        $dropzone = $document->getDrop()->getDropzone();

        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
    }
}
