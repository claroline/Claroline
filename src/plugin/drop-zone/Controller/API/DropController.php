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
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\DropzoneTool;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Entity\Team;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/dropzone", options={"expose"=true})
 *
 * @todo use crud
 */
class DropController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var DropzoneManager */
    private $manager;

    /** @var ObjectManager */
    private $om;

    public function __construct(
        FinderProvider $finder,
        DropzoneManager $manager,
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->finder = $finder;
        $this->manager = $manager;
        $this->om = $om;
        $this->authorization = $authorization;
    }

    /**
     * @Route("/drop/{id}", name="claro_dropzone_drop_fetch", methods={"GET"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function getAction(Drop $drop): JsonResponse
    {
        $dropzone = $drop->getDropzone();
        /* TODO: checks if current user can edit resource or view this drop */
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);

        return new JsonResponse($this->manager->serializeDrop($drop));
    }

    /**
     * @Route("/{id}/drops/search", name="claro_dropzone_drops_search", methods={"GET"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function listAction(Dropzone $dropzone, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $data = $this->finder->search(Drop::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['dropzone' => $dropzone->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * Initializes a Drop for the current User or Team.
     *
     * @Route("/{id}/drops/{teamId}", name="claro_dropzone_drop_create", defaults={"teamId"=null}, methods={"POST"})
     * @EXT\ParamConverter("dropzone", class="Claroline\DropZoneBundle\Entity\Dropzone", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("team", class="Claroline\TeamBundle\Entity\Team", options={"mapping": {"teamId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function createAction(Dropzone $dropzone, User $user, Team $team = null): JsonResponse
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
     * Delete Drop.
     *
     * @Route("/{id}/drops", name="claro_dropzone_drop_delete", methods={"DELETE"})
     * @EXT\ParamConverter("dropzone", class="Claroline\DropZoneBundle\Entity\Dropzone", options={"mapping": {"id": "uuid"}})
     */
    public function deleteAction(Dropzone $dropzone, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        if (!$dropzone->hasLockDrops()) {
            $drops = $this->decodeIdsString($request, Drop::class);

            $this->om->startFlushSuite();
            foreach ($drops as $drop) {
                $this->manager->deleteDrop($drop);
            }
            $this->om->endFlushSuite();
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Submits Drop.
     *
     * @Route("/drop/{id}/submit", name="claro_dropzone_drop_submit", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function submitAction(Drop $drop, User $user): JsonResponse
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
     * @Route("/drop/{id}/submission/cancel", name="claro_dropzone_drop_submission_cancel", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function cancelAction(Drop $drop): JsonResponse
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
     * @Route("/drop/{id}/type/{type}", name="claro_dropzone_documents_add", methods={"POST"})
     * @EXT\ParamConverter("drop", class="Claroline\DropZoneBundle\Entity\Drop", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function addDocumentAction(Drop $drop, string $type, User $user, Request $request): JsonResponse
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
     * @Route("/document/{id}", name="claro_dropzone_document_delete", methods={"DELETE"})
     * @EXT\ParamConverter(
     *     "document",
     *     class="Claroline\DropZoneBundle\Entity\Document",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function deleteDocumentAction(Document $document, User $user): JsonResponse
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
     * @Route(
     *     "/drop/{id}/revision/{revision}/type/{type}/manager",
     *     name="claro_dropzone_manager_documents_add",
     *     methods={"POST"}
     * )
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"revision": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param int $type
     */
    public function addManagerDocumentAction(Drop $drop, Revision $revision, $type, User $user, Request $request): JsonResponse
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
     * @Route("/document/{id}/manager", name="claro_dropzone_manager_document_delete", methods={"DELETE"})
     * @EXT\ParamConverter(
     *     "document",
     *     class="Claroline\DropZoneBundle\Entity\Document",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function deleteManagerDocumentAction(Document $document): JsonResponse
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
     * @Route("/drop/{id}/unlock", name="claro_dropzone_drop_unlock", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function dropUnlockAction(Drop $drop): JsonResponse
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
     * @Route("/drop/{id}/unlock/user", name="claro_dropzone_drop_unlock_user", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function dropUserUnlockAction(Drop $drop): JsonResponse
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
     */
    public function toolExecuteAction(DropzoneTool $tool, Document $document): JsonResponse
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
     * Downloads drops documents into a ZIP archive.
     *
     * @Route("/drops/download", name="claro_dropzone_drops_download", methods={"GET"})
     */
    public function downloadAction(Request $request): StreamedResponse
    {
        $drops = $this->decodeIdsString($request, Drop::class);
        /** @var Dropzone $dropzone */
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
     * @Route("/{id}/drops/csv", name="claro_dropzone_drops_csv", methods={"GET"})
     * @EXT\ParamConverter("dropzone", class="Claroline\DropZoneBundle\Entity\Dropzone", options={"mapping": {"id": "uuid"}})
     */
    public function exportCsvAction(Dropzone $dropzone): StreamedResponse
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $fileName = "results-{$dropzone->getResourceNode()->getSlug()}";
        $fileName = TextNormalizer::toKey($fileName);

        $drops = $this->finder->searchEntities(Drop::class, [
            'filters' => ['dropzone' => $dropzone->getUuid()],
        ]);

        return new StreamedResponse(function () use ($drops) {
            // Prepare CSV file
            $handle = fopen('php://output', 'w+');

            // Create header
            fputcsv($handle, [
                'first_name',
                'last_name',
                'score',
                'date',
                'finished',
                'corrector_first_name',
                'corrector_last_name',
                'corrector_score',
                'corrector_comment',
            ], ';', '"');

            /** @var Drop $drop */
            foreach ($drops['data'] as $drop) {
                $dropData = [
                    $drop->getUser()->getFirstName(),
                    $drop->getUser()->getLastName(),
                    $drop->getScore(),
                    DateNormalizer::normalize($drop->getDropDate()),
                    $drop->isFinished(),
                ];

                if (empty($drop->getCorrections())) {
                    fputcsv($handle, array_merge($dropData, [null, null, null, null]), ';', '"');
                } else {
                    // add a line for each correction
                    foreach ($drop->getCorrections() as $correction) {
                        fputcsv($handle, array_merge($dropData, [
                            $correction->getUser()->getFirstName(),
                            $correction->getUser()->getLastName(),
                            $correction->getScore(),
                            $correction->getComment(),
                        ]), ';', '"');
                    }
                }
            }

            fclose($handle);

            return $handle;
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]);
    }

    /**
     * @Route("/drop/{id}/next", name="claro_dropzone_drop_next")
     * @EXT\ParamConverter("drop", class="Claroline\DropZoneBundle\Entity\Drop", options={"mapping": {"id": "uuid"}})
     */
    public function nextAction(Drop $drop, Request $request): JsonResponse
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
        $data = $this->finder->get(Drop::class)->find($filters, $sortBy, 0, -1, false);
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
     * @Route("/drop/{id}/previous", name="claro_dropzone_drop_previous")
     * @EXT\ParamConverter("drop", class="Claroline\DropZoneBundle\Entity\Drop", options={"mapping": {"id": "uuid"}})
     */
    public function previousAction(Drop $drop, Request $request): JsonResponse
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
        $data = $this->finder->get(Drop::class)->find($filters, $sortBy, 0, -1, false);
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
}
