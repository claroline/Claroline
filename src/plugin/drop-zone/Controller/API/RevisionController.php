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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
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
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /** @var DropzoneManager */
    private $manager;

    /**
     * RevisionController constructor.
     */
    public function __construct(AuthorizationCheckerInterface $authorization, DropzoneManager $manager)
    {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'droprevision';
    }

    public function getClass()
    {
        return Revision::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'create', 'update', 'list', 'deleteBulk'];
    }

    /**
     * Submits Drop for revision.
     *
     * @Route("/drop/{id}/submit/revision", name="claro_dropzone_drop_submit_for_revision", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function submitForRevisionAction(Drop $drop, User $user)
    {
        $dropzone = $drop->getDropzone();
        $this->checkPermission('OPEN', $dropzone->getResourceNode());
        $this->checkDropEdition($drop, $user);

        try {
            $revision = $this->manager->submitDropForRevision($drop, $user);

            return new JsonResponse([
                'drop' => $this->manager->serializeDrop($drop),
                'revision' => $this->manager->serializeRevision($revision),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/{id}/revisions/list", name="claro_dropzone_revisions_list", methods={"GET"})
     * @EXT\ParamConverter(
     *     "dropzone",
     *     class="Claroline\DropZoneBundle\Entity\Dropzone",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function revisionsListAction(Dropzone $dropzone, Request $request)
    {
        $this->checkPermission('EDIT', $dropzone->getResourceNode());

        $data = $this->finder->search(Revision::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['dropzone' => $dropzone->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/drop/{drop}/revisions/list", name="claro_dropzone_drop_revisions_list", methods={"GET"})
     * @EXT\ParamConverter(
     *     "drop",
     *     class="Claroline\DropZoneBundle\Entity\Drop",
     *     options={"mapping": {"drop": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function dropRevisionsListAction(Drop $drop, User $user, Request $request)
    {
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if (!$this->authorization->isGranted('EDIT', $collection) && $drop->getUser() !== $user && !in_array($user, $drop->getUsers())) {
            throw new AccessDeniedException();
        }

        $data = $this->finder->search(Revision::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['drop' => $drop->getUuid()]]
        ));

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/{id}/revision/drop", name="claro_dropzone_drop_from_revision_get", methods={"GET"})
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function dropFromRevisionFetcAction(Revision $revision, User $user)
    {
        $drop = $revision->getDrop();
        $dropzone = $drop->getDropzone();
        $collection = new ResourceCollection([$dropzone->getResourceNode()]);

        if (!$this->authorization->isGranted('EDIT', $collection) && $drop->getUser() !== $user && !in_array($user, $drop->getUsers())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->manager->serializeDrop($drop), 200);
    }

    /**
     * @Route(
     *     "/revision/{id}/next",
     *     name="claro_dropzone_revision_next"
     * )
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function nextRevisionAction(Revision $revision, Request $request)
    {
        $dropzone = $revision->getDrop()->getDropzone();

        $this->checkPermission('EDIT', $dropzone->getResourceNode());

        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Revision[] $data */
        $data = $this->finder->get(Revision::class)->find($filters, $sortBy, 0, -1, false);
        $next = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $revision->getUuid()) {
                $next = $position + 1;
            }
        }

        $nextRevision = array_key_exists($next, $data) ? $data[$next] : reset($data);

        return new JsonResponse($this->manager->serializeRevision($nextRevision), 200);
    }

    /**
     * @Route(
     *     "/revision/{id}/previous",
     *     name="claro_dropzone_revision_previous"
     * )
     * @EXT\ParamConverter(
     *     "revision",
     *     class="Claroline\DropZoneBundle\Entity\Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function previousRevisionAction(Revision $revision, Request $request)
    {
        $dropzone = $revision->getDrop()->getDropzone();

        $this->checkPermission('EDIT', $dropzone->getResourceNode());

        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['dropzone'] = $dropzone->getUuid();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Revision[] $data */
        $data = $this->finder->get(Revision::class)->find($filters, $sortBy, 0, -1, false);
        $previous = null;

        foreach ($data as $position => $value) {
            if ($value->getUuid() === $revision->getUuid()) {
                $previous = $position - 1;
            }
        }

        $previousDrop = array_key_exists($previous, $data) ? $data[$previous] : end($data);

        return new JsonResponse($this->manager->serializeRevision($previousDrop), 200);
    }

    private function checkPermission($permission, ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection([$resourceNode]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException();
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
}
