<?php

namespace Claroline\AnnouncementBundle\Controller\API;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages announces of an announcement resource.
 *
 * @EXT\Route("/{aggregateId}", options={"expose"=true})
 * @EXT\ParamConverter(
 *      "aggregate",
 *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
 *      options={"mapping": {"aggregateId": "uuid"}}
 * )
 */
class AnnouncementController
{
    use PermissionCheckerTrait;

    /** @var AnnouncementManager */
    private $manager;

    /**
     * AnnouncementController constructor.
     *
     * @DI\InjectParams({
     *     "manager"    = @DI\Inject("claroline.manager.announcement_manager"),
     *     "serializer" = @DI\Inject("claroline.serializer.announcement"),
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder"     = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param AnnouncementManager $managersuppression
     */
    public function __construct(AnnouncementManager $manager, AnnouncementSerializer $serializer, Crud $crud, ObjectManager $om, FinderProvider $finder)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->om = $om;
        $this->finder = $finder;
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
     *
     * @return JsonResponse
     */
    public function validateSendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $ids = $request->query->all()['filters']['roles'];
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
        $parameters = array_merge($all, ['hiddenFilters' => ['unionRole' => array_map(function ($role) {
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
     *
     * @return JsonResponse
     */
    public function sendAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $roles = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Role');

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
            ['unionRole' => array_map(function ($role) {
                return $role->getUuid();
            }, $roles),
        ]);

        $this->manager->sendMessage($announcement, $users);

        return new JsonResponse(null, 200);
    }

    public function getClass()
    {
        return Announcement::class;
    }

    protected function decodeRequest(Request $request)
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new InvalidDataException('Invalid request content sent.', []);
        }

        return $decodedRequest;
    }

    /**
     * @param Request $request
     * @param string  $class
     */
    protected function decodeIdsString(Request $request, $class)
    {
        $ids = $request->query->get('ids');
        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }
}
