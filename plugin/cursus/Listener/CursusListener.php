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

use Claroline\CoreBundle\Event\GenericDatasEvent;
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

/**
 * @DI\Service
 */
class CursusListener
{
    private $cursusManager;
    private $facetManager;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "facetManager"          = @DI\Inject("claroline.manager.facet_manager"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "serializer"            = @DI\Inject("jms_serializer")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        FacetManager $facetManager,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        Serializer $serializer
    ) {
        $this->cursusManager = $cursusManager;
        $this->facetManager = $facetManager;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->serializer = $serializer;
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
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
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
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_profile_courses_tab_options")
     *
     * @param GenericDatasEvent $event
     */
    public function onProfileCoursesTabOptionsRequest(GenericDatasEvent $event)
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
     * @param GenericDatasEvent $event
     */
    public function onLearnerClosedSessionsRequest(GenericDatasEvent $event)
    {
        $user = $event->getDatas();
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
}
