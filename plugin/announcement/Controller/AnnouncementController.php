<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Entity\AnnouncementsWidgetConfig;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementCreateEvent;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementDeleteEvent;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementEditEvent;
use Claroline\AnnouncementBundle\Form\AnnouncementsWidgetConfigurationType;
use Claroline\AnnouncementBundle\Form\AnnouncementType;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class AnnouncementController extends Controller
{
    private $announcementManager;
    private $authorization;
    private $eventDispatcher;
    private $formFactory;
    private $pagerFactory;
    private $request;
    private $templating;
    private $tokenStorage;
    private $translator;
    private $utils;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "announcementManager" = @DI\Inject("claroline.announcement.manager.announcement_manager"),
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "requestStack"        = @DI\Inject("request_stack"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "translator"          = @DI\Inject("translator"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        AnnouncementManager $announcementManager,
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        PagerFactory $pagerFactory,
        RequestStack $requestStack,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        Utilities $utils,
        WorkspaceManager $workspaceManager
    ) {
        $this->announcementManager = $announcementManager;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->pagerFactory = $pagerFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/announcement/list/aggregate/{aggregateId}/page/{page}",
     *     name = "claro_announcements_list",
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsList.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     * @param $page
     *
     * @return Response
     */
    public function announcementsListAction(AnnouncementAggregate $aggregate, $page = 1)
    {
        $collection = new ResourceCollection([$aggregate->getResourceNode()]);

        try {
            $this->checkAccess('EDIT', $aggregate);
            $announcements = $this->announcementManager->getAllAnnouncementsByAggregate($aggregate);
        } catch (AccessDeniedException $e) {
            $this->checkAccess('OPEN', $aggregate);
            $announcements = $this->announcementManager->getVisibleAnnouncementsByAggregate($aggregate);
        }
        $pager = $this->pagerFactory->createPagerFromArray($announcements, $page, 5);

        return [
            '_resource' => $aggregate,
            'announcements' => $pager,
            'resourceCollection' => $collection,
            'workspace' => $aggregate->getResourceNode()->getWorkspace(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/aggregate/{aggregateId}/create/form",
     *     name = "claro_announcement_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     *
     * @return Response
     */
    public function createFormAction(AnnouncementAggregate $aggregate)
    {
        $this->checkAccess('EDIT', $aggregate);

        $announcement = new Announcement();
        $announcement->setVisible(true);
        $form = $this->formFactory->create(new AnnouncementType(), $announcement);

        return [
            'form' => $form->createView(),
            'type' => 'create',
            '_resource' => $aggregate,
        ];
    }

    /**
     * @EXT\Route(
     *     "/aggregate/{aggregateId}/create",
     *     name = "claro_announcement_create"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     *
     * @return Response
     */
    public function createAction(AnnouncementAggregate $aggregate)
    {
        $this->checkAccess('EDIT', $aggregate);

        $user = $this->tokenStorage->getToken()->getUser();
        $announcement = new Announcement();
        $form = $this->formFactory->create(new AnnouncementType(), $announcement);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $visibleFrom = $announcement->getVisibleFrom();
            $visibleUntil = $announcement->getVisibleUntil();

            if (!is_null($visibleFrom) && !is_null($visibleUntil) && $visibleUntil <= $visibleFrom) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->translator->trans(
                        'visible_from_until_condition',
                        [],
                        'announcement'
                    )
                );

                return [
                    'form' => $form->createView(),
                    'type' => 'create',
                    '_resource' => $aggregate,
                ];
            }

            $announcement->setAggregate($aggregate);
            $announcement->setCreationDate($now);

            if ($announcement->isVisible()) {
                if (is_null($visibleFrom) || $visibleFrom < $now) {
                    $announcement->setPublicationDate($now);
                } else {
                    $announcement->setPublicationDate($visibleFrom);
                }
            }
            $announcement->setCreator($user);
            $this->announcementManager->insertAnnouncement($announcement);

            if ($form->get('notify_user')->getData()) {
                $this->announcementManager->sendMessage($announcement);
            }

            $this->eventDispatcher->dispatch(
                'log',
                new LogAnnouncementCreateEvent($aggregate, $announcement)
            );

            return $this->redirect(
                $this->generateUrl(
                    'claro_announcements_list',
                    ['aggregateId' => $aggregate->getId()]
                )
            );
        }

        return [
            'form' => $form->createView(),
            'type' => 'create',
            '_resource' => $aggregate,
        ];
    }

    /**
     * @EXT\Route(
     *     "/announcement/{announcementId}/edit/form",
     *     name = "claro_announcement_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"id" = "announcementId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param Announcement $announcement
     *
     * @return Response
     */
    public function announcementEditFormAction(Announcement $announcement)
    {
        $resource = $announcement->getAggregate();
        $this->checkAccess('EDIT', $resource);

        $form = $this->formFactory->create(new AnnouncementType(), $announcement);

        return [
            'form' => $form->createView(),
            'type' => 'edit',
            'announcement' => $announcement,
            '_resource' => $resource,
        ];
    }

    /**
     * @EXT\Route(
     *     "/announcement/{announcementId}/edit",
     *     name = "claro_announcement_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"id" = "announcementId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param Announcement $announcement
     *
     * @return Response
     */
    public function announcementEditAction(Announcement $announcement)
    {
        $resource = $announcement->getAggregate();
        $this->checkAccess('EDIT', $resource);
        $form = $this->formFactory->create(new AnnouncementType(), $announcement);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $visibleFrom = $announcement->getVisibleFrom();
            $visibleUntil = $announcement->getVisibleUntil();

            if (!is_null($visibleFrom) && !is_null($visibleUntil) && $visibleUntil <= $visibleFrom) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->translator->trans('visible_from_until_condition', [], 'announcement')
                );

                return [
                    'form' => $form->createView(),
                    'type' => 'edit',
                    'announcement' => $announcement,
                    '_resource' => $resource,
                ];
            }

            if (!$announcement->isVisible()) {
                $announcement->setPublicationDate(null);
            } else {
                if (is_null($visibleFrom) || $visibleFrom < $now) {
                    $announcement->setPublicationDate($now);
                } else {
                    $announcement->setPublicationDate($visibleFrom);
                }
            }
            $this->announcementManager->insertAnnouncement($announcement);

            if ($form->get('notify_user')->getData()) {
                $this->announcementManager->sendMessage($announcement);
            }

            $this->eventDispatcher->dispatch(
                'log',
                new LogAnnouncementEditEvent(
                    $announcement->getAggregate(),
                    $announcement
                )
            );

            return $this->redirect(
                $this->generateUrl(
                    'claro_announcements_list',
                    ['aggregateId' => $resource->getId()]
                )
            );
        }

        return [
            'form' => $form->createView(),
            'type' => 'edit',
            'announcement' => $announcement,
            '_resource' => $resource,
        ];
    }

    /**
     * @EXT\Route(
     *     "/announcement/{announcementId}/delete",
     *     name = "claro_announcement_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"id" = "announcementId", "strictId" = true}
     * )
     *
     * @param Announcement $announcement
     *
     * @return Response
     */
    public function announcementDeleteAction(Announcement $announcement)
    {
        $resource = $announcement->getAggregate();
        $this->checkAccess('EDIT', $resource);
        $this->announcementManager->deleteAnnouncement($announcement);

        $this->eventDispatcher->dispatch(
            'log',
            new LogAnnouncementDeleteEvent($resource, $announcement)
        );

        return new Response(204);
    }

    /**
     * @EXT\Route(
     *     "/announcement/widget/{widgetInstance}",
     *     name="claro_announcement_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsWidget.html.twig")
     *
     * Renders announcement widget.
     */
    public function announcementsWidgetAction(WidgetInstance $widgetInstance)
    {
        return ['widgetInstance' => $widgetInstance];
    }

    /**
     * @EXT\Route(
     *     "/announcement/widget/{widgetInstance}/page/{page}",
     *     name="claro_announcement_widget_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Renders announcements in a pager.
     *
     * @param WidgetInstance $widgetInstance
     * @param int            $page
     *
     * @return Response
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
            $template = 'ClarolineAnnouncementBundle::announcementsDesktopWidgetPager.html.twig';
        } else {
            $data = $this->announcementManager->getVisibleAnnouncementsByWorkspace($workspace, $roles);
            $template = 'ClarolineAnnouncementBundle::announcementsWorkspaceWidgetPager.html.twig';
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
        $pager = $this->pagerFactory->createPagerFromArray($announcements, $page, 5);

        return new Response($this->templating->render($template, ['widgetInstance' => $widgetInstance, 'datas' => $pager]));
    }

    /**
     * @EXT\Route(
     *     "/announcements/widget/{widgetInstance}/configure/form",
     *     name="claro_announcements_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsWidgetConfigureForm.html.twig")
     */
    public function announcementsWidgetConfigureFormAction(WidgetInstance $widgetInstance)
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
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsWidgetConfigureForm.html.twig")
     */
    public function announcementsWidgetConfigureAction(AnnouncementsWidgetConfig $config)
    {
        $form = $this->formFactory->create(new AnnouncementsWidgetConfigurationType());
        $form->handleRequest($this->request);

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

    /**
     * @EXT\Route(
     *     "/announcement/{announcement}/send/mail",
     *     name="claro_announcement_send_mail",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "usersIds"}
     * )
     *
     * Sends announcement by mail
     *
     * @return Response
     */
    public function announcementSendMailAction(Announcement $announcement, array $users)
    {
        $resource = $announcement->getAggregate();
        $this->checkAccess('EDIT', $resource);

        if (count($users) > 0) {
            $this->announcementManager->sendMail($announcement, $users);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string                                                 $permission
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    private function checkAccess($permission, AbstractResource $resource)
    {
        $collection = new ResourceCollection([$resource->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
