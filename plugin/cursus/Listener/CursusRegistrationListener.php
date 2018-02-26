<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Listener;

use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Menu\GroupAdditionalActionEvent;
use Claroline\CoreBundle\Menu\UserAdditionalActionEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class CursusRegistrationListener
{
    private $cursusManager;
    private $httpKernel;
    private $request;
    private $router;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager"),
     *     "httpKernel"    = @DI\Inject("http_kernel"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "router"       = @DI\Inject("router"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator
    ) {
        $this->cursusManager = $cursusManager;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("administration_tool_claroline_cursus_tool_registration")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineCursusBundle:CursusRegistration:cursusToolRegistrationIndex';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event)
    {
        $log = $event->getLog();
        $action = $log->getAction();
        $user = $log->getReceiver();
        $group = $log->getReceiverGroup();
        $sessions = ['learner' => [], 'tutor' => []];

        if ($action === 'group-add_user') {
            $multipleCursus = $this->cursusManager->getCursusByGroup($group);
            //check if the user is already persisted in database
            $force = $user->getId() ? false : true;
            $this->cursusManager->registerUserToMultipleCursus($multipleCursus, $user, true, false, $force);

            $sessionGroups = $this->cursusManager->getSessionGroupsByGroup($group);

            foreach ($sessionGroups as $sessionGroup) {
                $groupType = $sessionGroup->getGroupType();

                if ($groupType === 0) {
                    $sessions['learner'][] = $sessionGroup->getSession();
                } elseif ($groupType === 1) {
                    $sessions['tutor'][] = $sessionGroup->getSession();
                }
            }

            if (count($sessions['learner']) > 0) {
                $this->cursusManager->registerUsersToSessions($sessions['learner'], [$user], 0, $force);
            }

            if (count($sessions['tutor']) > 0) {
                $this->cursusManager->registerUsersToSessions($sessions['tutor'], [$user], 1, $force);
            }
        } elseif ($action === 'group-remove_user') {
            $multipleCursus = $this->cursusManager->getCursusByGroup($group);
            $cursusUsers = $this->cursusManager->getCursusUsersFromCursusAndUsers(
                $multipleCursus,
                [$user]
            );
            $this->cursusManager->deleteCursusUsers($cursusUsers);

            $sessionGroups = $this->cursusManager->getSessionGroupsByGroup($group);

            foreach ($sessionGroups as $sessionGroup) {
                $groupType = $sessionGroup->getGroupType();

                if ($groupType === 0) {
                    $sessions['learner'][] = $sessionGroup->getSession();
                } elseif ($groupType === 1) {
                    $sessions['tutor'][] = $sessionGroup->getSession();
                }
            }
            $sessionUsers = [];

            if (count($sessions['learner']) > 0) {
                $sessionUsers = $this->cursusManager->getSessionUsersBySessionsAndUsers(
                    $sessions['learner'],
                    [$user],
                    0
                );
            }

            if (count($sessions['tutor']) > 0) {
                $sessionTutors = $this->cursusManager->getSessionUsersBySessionsAndUsers(
                    $sessions['tutor'],
                    [$user],
                    1
                );
                $sessionUsers = array_merge($sessionUsers, $sessionTutors);
            }
            $this->cursusManager->deleteCourseSessionUsers($sessionUsers);
        }
    }

    /**
     * @DI\Observe("claroline_user_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\UserAdditionalActionEvent $event
     */
    public function onUserActionMenuRender(UserAdditionalActionEvent $event)
    {
        $user = $event->getUser();
        $url = $this->router->generate(
            'claro_cursus_user_sessions_management',
            ['user' => $user->getUuid()]
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('user_sessions_management', [], 'cursus'),
            ['uri' => $url]
        )->setExtra('icon', 'fa fa-list-alt');

        return $menu;
    }

    /**
     * @DI\Observe("claroline_group_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\GroupAdditionalActionEvent $event
     */
    public function onGroupActionMenuRender(GroupAdditionalActionEvent $event)
    {
        $group = $event->getGroup();
        $url = $this->router->generate(
            'claro_cursus_group_sessions_management',
            ['group' => $group->getId()]
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('user_sessions_management', [], 'cursus'),
            ['uri' => $url]
        )->setExtra('icon', 'fa fa-list-alt');

        return $menu;
    }
}
