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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/droprevision")
 */
class RevisionController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly FinderProvider $finder,
        private readonly DropzoneManager $manager
    ) {
    }

    public function getName(): string
    {
        return 'droprevision';
    }

    public function getClass(): string
    {
        return Revision::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list', 'deleteBulk'];
    }

    /**
     * Submits Drop for revision.
     *
     * @Route("/drop/{id}/submit/revision", name="claro_dropzone_drop_submit_for_revision", methods={"PUT"})
     *
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function submitForRevisionAction(Drop $drop, User $user): JsonResponse
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode(), [], true);
        $this->checkDropEdition($drop, $user);

        try {
            $revision = $this->manager->submitDropForRevision($drop, $user);

            return new JsonResponse([
                'drop' => $this->serializer->serialize($drop),
                'revision' => $this->serializer->serialize($revision),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/{id}/revisions/list", name="claro_dropzone_revisions_list", methods={"GET"})
     *
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function revisionsListAction(Dropzone $dropzone, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $data = $this->crud->list(Revision::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['dropzone' => $dropzone->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/drop/{drop}/revisions/list", name="claro_dropzone_drop_revisions_list", methods={"GET"})
     *
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"drop": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function dropRevisionsListAction(Drop $drop, User $user, Request $request): JsonResponse
    {
        $dropzone = $drop->getDropzone();
        if (!$this->authorization->isGranted('EDIT', $dropzone->getResourceNode()) && $drop->getUser() !== $user && !in_array($user, $drop->getUsers())) {
            throw new AccessDeniedException();
        }

        $data = $this->crud->list(Revision::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['drop' => $drop->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/{id}/revision/drop", name="claro_dropzone_drop_from_revision_get", methods={"GET"})
     *
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function dropFromRevisionFetchAction(Revision $revision, User $user): JsonResponse
    {
        $drop = $revision->getDrop();
        $dropzone = $drop->getDropzone();

        if (!$this->authorization->isGranted('EDIT', $dropzone->getResourceNode()) && $drop->getUser() !== $user && !in_array($user, $drop->getUsers())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->serializer->serialize($drop), 200);
    }

    /**
     * @Route(
     *     "/revision/{id}/next",
     *     name="claro_dropzone_revision_next"
     * )
     *
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     */
    public function nextRevisionAction(Revision $revision, Request $request): JsonResponse
    {
        $dropzone = $revision->getDrop()->getDropzone();

        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        // array map is not even needed; objects are fine here
        /** @var Revision[] $data */
        $data = $this->finder->get(Revision::class)->find($filters, $sortBy, 0, -1, false);
        $next = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $revision->getUuid()) {
                $next = $position + 1;
            }
        }

        $nextRevision = array_key_exists($next, $data) ? $data[$next] : reset($data);

        return new JsonResponse($this->serializer->serialize($nextRevision), 200);
    }

    /**
     * @Route(
     *     "/revision/{id}/previous",
     *     name="claro_dropzone_revision_previous"
     * )
     *
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     */
    public function previousRevisionAction(Revision $revision, Request $request): JsonResponse
    {
        $dropzone = $revision->getDrop()->getDropzone();

        $this->checkPermission('EDIT', $dropzone->getResourceNode(), [], true);

        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        // array map is not even needed; objects are fine here
        /** @var Revision[] $data */
        $data = $this->finder->get(Revision::class)->find($filters, $sortBy, 0, -1, false);
        $previous = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $revision->getUuid()) {
                $previous = $position - 1;
            }
        }

        $previousDrop = array_key_exists($previous, $data) ? $data[$previous] : end($data);

        return new JsonResponse($this->serializer->serialize($previousDrop), 200);
    }

    private function checkDropEdition(Drop $drop, User $user): void
    {
        $dropzone = $drop->getDropzone();

        if ($this->authorization->isGranted('EDIT', $dropzone->getResourceNode())) {
            return;
        }

        if ($dropzone->isDropEnabled()) {
            if ($drop->getUser() === $user || in_array($user, $drop->getUsers())) {
                return;
            }
        }

        throw new AccessDeniedException();
    }
}
