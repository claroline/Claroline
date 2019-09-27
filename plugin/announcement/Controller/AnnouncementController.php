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
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages announces of an announcement resource.
 *
 * @EXT\Route("/announcement/{aggregateId}", options={"expose"=true})
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

    /**
     * AnnouncementController constructor.
     *
     * @DI\InjectParams({
     *     "manager"    = @DI\Inject("claroline.manager.announcement_manager"),
     *     "serializer" = @DI\Inject("Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer"),
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder"     = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param AnnouncementManager    $manager
     * @param AnnouncementSerializer $serializer
     * @param Crud                   $crud
     * @param ObjectManager          $om
     * @param FinderProvider         $finder
     */
    public function __construct(
        AnnouncementManager $manager,
        AnnouncementSerializer $serializer,
        Crud $crud,
        ObjectManager $om,
        FinderProvider $finder
    ) {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->om = $om;
        $this->finder = $finder;

        $this->roleRepo = $om->getRepository(Role::class);
    }

    public function getClass()
    {
        return Announcement::class;
    }

    /**
     * Creates a new announce.
     *
     * @EXT\Route("/", name="claro_announcement_create")
     * @EXT\Method("POST")
     *
     * @param AnnouncementAggregate $aggregate
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function createAction(AnnouncementAggregate $aggregate, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $data = $this->decodeRequest($request);
        $data['aggregate'] = ['id' => $aggregate->getUuid()];

        /** @var Announcement $announcement */
        $announcement = $this->crud->create($this->getClass(), $data, [
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
     * @EXT\Route("/{id}", name="claro_announcement_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function updateAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
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
     * @EXT\Route("/{id}", name="claro_announcement_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     *
     * @return JsonResponse
     */
    public function deleteAction(AnnouncementAggregate $aggregate, Announcement $announcement)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        $this->crud->delete($announcement, ['announcement_aggregate' => $aggregate]);

        return new JsonResponse(null, 204);
    }

    /**
     * Sends an announce (in current implementation, it's sent by email).
     *
     * @EXT\Route("/{id}/validate", name="claro_announcement_validate")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function validateSendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $ids = $request->query->all()['filters']['roles'];

        /** @var Role[] $roles */
        $roles = $this->om->findList(Role::class, 'uuid', $ids);
        $node = $announcement->getAggregate()->getResourceNode();

        $rights = $node->getRights();

        if (0 === count($roles)) {
            foreach ($rights as $right) {
                //1 is the default "open" mask
                if ($right->getMask() & 1) {
                    $roles[] = $right->getRole();
                }
            }

            $roles[] = $this->roleRepo->findOneBy([
                'name' => 'ROLE_WS_MANAGER_'.$node->getWorkspace()->getUuid(),
            ]);
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
     * @EXT\Route("/{id}/send", name="claro_announcement_send")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function sendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        /** @var Role[] $roles */
        $roles = $this->decodeIdsString($request, Role::class);

        $node = $announcement->getAggregate()->getResourceNode();

        $rights = $node->getRights();

        if (0 === count($roles)) {
            foreach ($rights as $right) {
                //1 is the default "open" mask
                if ($right->getMask() & 1) {
                    $roles[] = $right->getRole();
                }
            }

            $roles[] = $this->roleRepo->findOneBy([
              'name' => 'ROLE_WS_MANAGER_'.$node->getWorkspace()->getUuid(),
          ]);
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
