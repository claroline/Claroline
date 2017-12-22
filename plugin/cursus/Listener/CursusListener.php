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

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class CursusListener
{
    private $configHandler;
    private $cursusManager;
    private $facetManager;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $serializer;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "configHandler"         = @DI\Inject("claroline.config.platform_config_handler"),
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "facetManager"          = @DI\Inject("claroline.manager.facet_manager"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "serializer"            = @DI\Inject("jms_serializer"),
     *     "translator"            = @DI\Inject("translator")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        CursusManager $cursusManager,
        FacetManager $facetManager,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        Serializer $serializer,
        TranslatorInterface $translator
    ) {
        $this->configHandler = $configHandler;
        $this->cursusManager = $cursusManager;
        $this->facetManager = $facetManager;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("administration_tool_claroline_cursus_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineCursusBundle:API\AdminManagement:index';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_cursusbundle")
     *
     * @param DisplayToolEvent $event
     */
    public function onPluginOptionsOpen(PluginOptionsEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineCursusBundle:Cursus:pluginConfigureForm';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_profile_courses_tab_options")
     *
     * @param GenericDataEvent $event
     */
    public function onProfileCoursesTabOptionsRequest(GenericDataEvent $event)
    {
        $data = [];
        $facetPreferences = $this->facetManager->getVisiblePublicPreference();
        $data['displayCourses'] = $facetPreferences['baseData'] ?
            $this->platformConfigHandler->getParameter('cursus_enable_courses_profile_tab') :
            false;
        $data['displayWorkspace'] = $this->platformConfigHandler->getParameter('cursus_enable_ws_in_courses_profile_tab');
        $event->setResponse($data);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_learner_closed_sessions")
     *
     * @param GenericDataEvent $event
     */
    public function onLearnerClosedSessionsRequest(GenericDataEvent $event)
    {
        $user = $event->getData();
        $facetPreferences = $this->facetManager->getVisiblePublicPreference();
        $enabled = $facetPreferences['baseData'] ?
            $this->platformConfigHandler->getParameter('cursus_enable_courses_profile_tab') :
            false;
        $sessions = $enabled && $user ? $this->cursusManager->getClosedSessionsByUser($user) : [];
        $response = $this->serializer->serialize(
            $sessions,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_workspace_claroline_session_events_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceSessionEventTool(DisplayToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineCursusBundle:API\SessionEventsTool:index';
        $params['workspace'] = $event->getWorkspace()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_external_agenda_events")
     *
     * @param GenericDataEvent $event
     */
    public function onAgendaEventsRequest(GenericDataEvent $event)
    {
        $events = $event->getResponse() ? $event->getResponse() : [];
        $data = $event->getData();
        $type = $data['type'];
        $sessionEvents = [];

        if ($type === 'workspace') {
            $workspace = $data['workspace'];
            $sessionEvents = $this->cursusManager->getSessionEventsByWorkspace($workspace);
        } elseif ($type === 'desktop') {
            $user = $data['user'];
            $sessionEvents = $this->configHandler->getParameter('cursus_display_user_events_in_desktop_agenda') ?
                $this->cursusManager->getSessionEventsByUser($user) :
                [];
        }
        foreach ($sessionEvents as $sessionEvent) {
            $sessionWorkspace = $sessionEvent->getSession()->getWorkspace();
            $description = $sessionEvent->getDescription() ? $sessionEvent->getDescription() : '';
            $location = $sessionEvent->getLocation();
            $locationExtra = $sessionEvent->getLocationExtra();
            $teachers = $sessionEvent->getTutors();

            if ($location || $locationExtra) {
                $description .= $description ?
                    '<hr/><b>'.$this->translator->trans('location', [], 'platform').'</b><br/>' :
                    '';
                if ($location) {
                    $description .= '<div>'.
                        $location->getName().
                        '<br/>'.
                        $location->getStreet().', '.$location->getStreetNumber();
                    $description .= $location->getBoxNumber() ? '/'.$location->getBoxNumber() : '';
                    $description .= '<br/>'.
                        $location->getPc().' '.$location->getTown().
                        '<br/>'.
                        $location->getCountry();
                    $description .= $location->getPhone() ? '<br/>'.$location->getPhone() : '';
                    $description .= '</div>';
                }
                $description .= $locationExtra ? $locationExtra : '';
            }
            if (count($teachers) > 0) {
                $description .= $description ? '<hr/>' : '';
                $description .= '<b>'.$this->translator->trans('tutors', [], 'cursus').'</b>'.
                    '<br/>'.
                    '<ul>';

                foreach ($teachers as $teacher) {
                    $description .= '<li>'.
                        $teacher->getFirstName().' '.$teacher->getLastName().
                        '</li>';
                }
                $description .= '</ul>';
            }
            $events[] = [
                'title' => $sessionEvent->getName(),
                'start' => $sessionEvent->getStartDate()->format(\DateTime::ISO8601),
                'end' => $sessionEvent->getEndDate()->format(\DateTime::ISO8601),
                'description' => $description,
                'color' => '#578E48',
                'allDay' => false,
                'isTask' => false,
                'isTaskDone' => false,
                'isEditable' => false,
                'workspace_id' => $sessionWorkspace ? $sessionWorkspace->getId() : null,
                'workspace_name' => $sessionWorkspace ? $sessionWorkspace->getName() : null,
                'className' => 'pointer-hand session_event_'.$sessionEvent->getId(),
                'durationEditable' => false,
            ];
        }
        $event->setResponse($events);
    }
}
