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

use Exception;
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

#[Route(path: '/droprevision', name: 'apiv2_droprevision_')]
class RevisionController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly DropzoneManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'droprevision';
    }

    public static function getClass(): string
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
     *
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/drop/{id}/submit/revision', name: 'submit_for_revision', methods: ['PUT'])]
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
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     *
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/{id}/revisions/list', name: 'dropzone_list', methods: ['GET'])]
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
     *
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"drop": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/drop/{drop}/revisions/list', name: 'drop_list', methods: ['GET'])]
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
     *
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/{id}/revision/drop', name: 'drop_get', methods: ['GET'])]
    public function dropFromRevisionFetchAction(Revision $revision, User $user): JsonResponse
    {
        $drop = $revision->getDrop();
        $dropzone = $drop->getDropzone();

        if (!$this->authorization->isGranted('EDIT', $dropzone->getResourceNode()) && $drop->getUser() !== $user && !in_array($user, $drop->getUsers())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->serializer->serialize($drop), 200);
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
