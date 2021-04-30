<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages announces of an announcement resource.
 *
 * @Route("/announcement/{aggregateId}", options={"expose"=true})
 * @EXT\ParamConverter(
 *      "aggregate",
 *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
 *      options={"mapping": {"aggregateId": "uuid"}}
 * )
 */
class AnnouncementController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    /** @var AnnouncementManager */
    private $manager;

    /** @var AnnouncementSerializer */
    private $serializer;

    /** @var Crud */
    private $crud;

    /** @var ObjectManager */
    private $om;

    /** @var FinderProvider */
    private $finder;

    /** @var RoleRepository */
    private $roleRepo;

    public function __construct(
        AnnouncementManager $manager,
        AnnouncementSerializer $serializer,
        Crud $crud,
        ObjectManager $om,
        FinderProvider $finder,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->om = $om;
        $this->finder = $finder;
        $this->authorization = $authorization;

        $this->roleRepo = $om->getRepository(Role::class);
    }

    public function getClass()
    {
        return Announcement::class;
    }

    /**
     * Creates a new announce.
     *
     * @Route("/", name="claro_announcement_create", methods={"POST"})
     */
    public function createAction(AnnouncementAggregate $aggregate, Request $request): JsonResponse
    {
        $this->checkPermission('CREATE-ANNOUNCE', $aggregate->getResourceNode(), [], true);
        $data = $this->decodeRequest($request);
        $data['aggregate'] = ['id' => $aggregate->getUuid()];

        /** @var Announcement $announcement */
        $announcement = $this->crud->create($this->getClass(), $data, [
            Crud::NO_PERMISSIONS, // this has already been checked
            'announcement_aggregate' => $aggregate,
        ]);

        return new JsonResponse(
            $this->serializer->serialize($announcement),
            201
        );
    }

    /**
     * Updates an existing announce.
     *
     * @Route("/{id}", name="claro_announcement_update", methods={"PUT"})
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     */
    public function updateAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        /** @var Announcement $announcement */
        $announcement = $this->crud->update($this->getClass(), $this->decodeRequest($request), [
          'announcement_aggregate' => $aggregate,
        ]);

        return new JsonResponse(
            $this->serializer->serialize($announcement),
            201
        );
    }

    /**
     * Deletes an announce.
     *
     * @Route("/{id}", name="claro_announcement_delete", methods={"DELETE"})
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     */
    public function deleteAction(AnnouncementAggregate $aggregate, Announcement $announcement): JsonResponse
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        $this->crud->delete($announcement, ['announcement_aggregate' => $aggregate]);

        return new JsonResponse(null, 204);
    }

    /**
     * Sends an announce (in current implementation, it's sent by email).
     *
     * @Route("/{id}/validate", name="claro_announcement_validate", methods={"GET"})
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     */
    public function validateSendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $ids = $request->query->all()['filters']['roles'];

        /** @var Role[] $roles */
        $roles = $this->om->findList(Role::class, 'uuid', $ids);
        $node = $announcement->getAggregate()->getResourceNode();

        $rights = $node->getRights();

        if (0 === count($roles)) {
            foreach ($rights as $right) {
                // 1 is the default "open" mask (there should be a better way to do it)
                if ($right->getMask() & 1) {
                    $roles[] = $right->getRole();
                }
            }
        }

        $all = $request->query->all();
        unset($all['filters']['roles']);
        $parameters = array_merge($all, ['hiddenFilters' => ['unionRole' => array_map(function (Role $role) {
            return $role->getUuid();
        }, $roles)]]);

        return new JsonResponse($this->finder->search(User::class, $parameters, [Options::SERIALIZE_MINIMAL]));
    }

    /**
     * Sends an announce (in current implementation, it's sent by email).
     *
     * @Route("/{id}/send", name="claro_announcement_send", methods={"POST"})
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     */
    public function sendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);

        $roles = [];
        if (!empty($data) && !empty($data['ids'])) {
            /** @var Role[] $roles */
            $roles = $this->om->findList(Role::class, 'uuid', $data['ids']);
        }

        $node = $announcement->getAggregate()->getResourceNode();

        $rights = $node->getRights();

        if (0 === count($roles)) {
            foreach ($rights as $right) {
                // 1 is the default "open" mask (there should be a better way to do it)
                if ($right->getMask() & 1) {
                    $roles[] = $right->getRole();
                }
            }
        }

        $users = $this->finder->fetch(
            User::class,
            ['unionRole' => array_map(function (Role $role) {
                return $role->getUuid();
            }, $roles),
        ]);

        $this->manager->sendMessage($announcement, $users);

        return new JsonResponse(null, 200);
    }
}
