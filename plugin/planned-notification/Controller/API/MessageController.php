<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="Claroline\PlannedNotificationBundle\Entity\Message",
 *     ignore={"exist", "list", "copyBulk", "schema", "find"}
 * )
 * @EXT\Route("/plannednotificationmessage")
 */
class MessageController extends AbstractCrudController
{
    /* var FinderProvider */
    protected $finder;

    /** @var PlannedNotificationManager */
    protected $manager;

    /** @var ObjectManager */
    protected $om;

    /**
     * MessageController constructor.
     *
     * @DI\InjectParams({
     *     "finder"  = @DI\Inject("claroline.api.finder"),
     *     "manager" = @DI\Inject("claroline.manager.planned_notification_manager"),
     *     "om"      = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param FinderProvider             $finder
     * @param PlannedNotificationManager $manager
     * @param ObjectManager              $om
     */
    public function __construct(FinderProvider $finder, PlannedNotificationManager $manager, ObjectManager $om)
    {
        $this->finder = $finder;
        $this->manager = $manager;
        $this->om = $om;
    }

    public function getName()
    {
        return 'planned_notification_message';
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/list",
     *     name="apiv2_plannednotificationmessage_workspace_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function messagesListAction(Workspace $workspace, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        $data = $this->finder->search('Claroline\PlannedNotificationBundle\Entity\Message', $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/messages/send",
     *     name="apiv2_plannednotificationmessage_messages_send"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function messagesSendAction(Request $request)
    {
        $query = $request->request->all();
        $messages = $this->om->findList('Claroline\PlannedNotificationBundle\Entity\Message', 'uuid', $query['messages']);
        $users = $this->om->findList('Claroline\CoreBundle\Entity\User', 'uuid', $query['users']);
        $this->manager->sendMessages($messages, $users);

        return new JsonResponse('success', 200);
    }
}
