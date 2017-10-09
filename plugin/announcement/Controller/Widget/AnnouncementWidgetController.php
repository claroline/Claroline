<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Controller\Widget;

use Claroline\AnnouncementBundle\Entity\AnnouncementsWidgetConfig;
use Claroline\AnnouncementBundle\Form\AnnouncementsWidgetConfigurationType;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementWidgetController extends Controller
{
    private $announcementManager;
    private $formFactory;
    private $pagerFactory;
    private $templating;
    private $tokenStorage;
    private $utils;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "announcementManager" = @DI\Inject("claroline.manager.announcement_manager"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param AnnouncementManager   $announcementManager
     * @param FormFactoryInterface  $formFactory
     * @param PagerFactory          $pagerFactory
     * @param TwigEngine            $templating
     * @param TokenStorageInterface $tokenStorage
     * @param Utilities             $utils
     * @param WorkspaceManager      $workspaceManager
     */
    public function __construct(
        AnnouncementManager $announcementManager,
        FormFactoryInterface $formFactory,
        PagerFactory $pagerFactory,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        WorkspaceManager $workspaceManager
    ) {
        $this->announcementManager = $announcementManager;
        $this->formFactory = $formFactory;
        $this->pagerFactory = $pagerFactory;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * Renders announcement widget.
     *
     * @EXT\Route(
     *     "/announcement/widget/{widgetInstance}",
     *     name="claro_announcement_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineAnnouncementBundle:Widget:list.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function announcementsWidgetAction(WidgetInstance $widgetInstance)
    {
        return ['widgetInstance' => $widgetInstance];
    }

    /**
     * Renders announcements in a pager.
     *
     * @EXT\Route(
     *     "/announcement/widget/{widgetInstance}/page/{page}",
     *     name="claro_announcement_widget_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineAnnouncementBundle:Widget:pager.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     * @param int            $page
     *
     * @return array
     */
    public function announcementsWidgetPagerAction(WidgetInstance $widgetInstance, $page = 1)
    {
        $workspace = $widgetInstance->getWorkspace();
        $isDesktop = is_null($workspace);
        $config = $this->announcementManager->getAnnouncementsWidgetConfig($widgetInstance);
        $selectedResourcesIds = $config->getAnnouncements();
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);
        $announcements = [];

        if ($isDesktop) {
            $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);
            $data = $this->announcementManager->getVisibleAnnouncementsByWorkspaces($workspaces, $roles);
        } else {
            $data = $this->announcementManager->getVisibleAnnouncementsByWorkspace($workspace, $roles);
        }

        if (count($selectedResourcesIds) > 0) {
            foreach ($data as $announcement) {
                if (in_array($announcement['resourceNodeId'], $selectedResourcesIds)) {
                    $announcements[] = $announcement;
                }
            }
        } else {
            $announcements = $data;
        }

        return [
            'widgetInstance' => $widgetInstance,
            'isDesktop' => $isDesktop,
            'datas' => $this->pagerFactory->createPagerFromArray($announcements, $page, 5),
        ];
    }

    /**
     * @EXT\Route(
     *     "/announcements/widget/{widgetInstance}/configure/form",
     *     name="claro_announcements_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineAnnouncementBundle:Widget:configure.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function configureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->announcementManager->getAnnouncementsWidgetConfig($widgetInstance);
        $announcements = $config->getAnnouncements();
        $form = $this->formFactory->create(new AnnouncementsWidgetConfigurationType($announcements));

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/announcements/widget/configure/config/{config}",
     *     name="claro_announcements_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineAnnouncementBundle:Widget:configure.html.twig")
     *
     * @param AnnouncementsWidgetConfig $config
     * @param Request                   $request
     *
     * @return array|JsonResponse
     */
    public function configureAction(AnnouncementsWidgetConfig $config, Request $request)
    {
        $form = $this->formFactory->create(new AnnouncementsWidgetConfigurationType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $resource = $form->get('resource')->getData();

            if ($resource) {
                $config->clearAnnouncements();
                $config->addAnnouncement($resource->getId());
            }
            $this->announcementManager->persistAnnouncementsWidgetConfig($config);

            return new JsonResponse('success', 204);
        } else {
            return ['form' => $form->createView(), 'config' => $config];
        }
    }
}
