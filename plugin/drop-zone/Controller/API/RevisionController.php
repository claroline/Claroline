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
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/droprevision")
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
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "manager"       = @DI\Inject("claroline.manager.dropzone_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param DropzoneManager               $manager
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
     * @EXT\Route("/drop/{id}/submit/revision", name="claro_dropzone_drop_submit_for_revision")
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
     * @EXT\Route("/{id}/revisions/list", name="claro_dropzone_revisions_list")
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
     * @EXT\Route("/drop/{drop}/revisions/list", name="claro_dropzone_drop_revisions_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "drop",
     *     class="ClarolineDropZoneBundle:Drop",
     *     options={"mapping": {"drop": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Drop    $drop
     * @param User    $user
     * @param Request $request
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
     * @EXT\Route("/{id}/revision/drop", name="claro_dropzone_drop_from_revision_get")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "revision",
     *     class="ClarolineDropZoneBundle:Revision",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Revision $revision
     * @param User     $user
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
