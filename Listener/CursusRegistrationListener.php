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
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class CursusRegistrationListener
{
    private $cursusManager;
    private $httpKernel;
    private $request;

    /**
     * @DI\InjectParams({
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager"),
     *     "httpKernel"    = @DI\Inject("http_kernel"),
     *     "requestStack"  = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    )
    {
        $this->cursusManager = $cursusManager;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("administration_tool_claroline_cursus_tool_registration")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCursusBundle:CursusRegistration:cursusToolRegistrationIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
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
        $user =  $log->getReceiver();
        $group = $log->getReceiverGroup();
        $sessions = array('learner' => array(), 'tutor' => array());

        if ($action === 'group-add_user') {
            $multipleCursus = $this->cursusManager->getCursusByGroup($group);
            $this->cursusManager->registerUserToMultipleCursus($multipleCursus, $user);

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
                $this->cursusManager->registerUsersToSessions($sessions['learner'], array($user), 0);
            }

            if (count($sessions['tutor']) > 0) {
                $this->cursusManager->registerUsersToSessions($sessions['tutor'], array($user), 1);
            }
        } elseif ($action === 'group-remove_user') {
            $multipleCursus = $this->cursusManager->getCursusByGroup($group);
            $cursusUsers = $this->cursusManager->getCursusUsersFromCursusAndUsers(
                $multipleCursus,
                array($user)
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
            $sessionUsers = array();

            if (count($sessions['learner']) > 0) {
                $sessionUsers = $this->cursusManager->getSessionUsersBySessionsAndUsers(
                    $sessions['learner'],
                    array($user),
                    0
                );
            }

            if (count($sessions['tutor']) > 0) {
                $sessionTutors = $this->cursusManager->getSessionUsersBySessionsAndUsers(
                    $sessions['tutor'],
                    array($user),
                    1
                );
                $sessionUsers = array_merge($sessionUsers, $sessionTutors);
            }
            $this->cursusManager->deleteCourseSessionUsers($sessionUsers);
        }
    }
}
