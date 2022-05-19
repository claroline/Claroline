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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/plannednotificationmessage")
 */
class MessageController extends AbstractCrudController
{
    /** @var PlannedNotificationManager */
    protected $manager;

    /**
     * MessageController constructor.
     */
    public function __construct(PlannedNotificationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getClass()
    {
        return 'Claroline\PlannedNotificationBundle\Entity\Message';
    }

    public function getIgnore()
    {
        return ['exist', 'list', 'copyBulk', 'schema', 'find'];
    }

    public function getName()
    {
        return 'planned_notification_message';
    }

    /**
     * @Route(
     *     "/workspace/{workspace}/list",
     *     name="apiv2_plannednotificationmessage_workspace_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
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
     * @Route(
     *     "/messages/send",
     *     name="apiv2_plannednotificationmessage_messages_send"
     * )
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
