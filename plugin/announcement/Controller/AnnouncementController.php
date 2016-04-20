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
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementCreateEvent;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementDeleteEvent;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementEditEvent;
use Claroline\AnnouncementBundle\Form\AnnouncementType;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AnnouncementController extends Controller
{
    private $announcementManager;
    private $eventDispatcher;
    private $formFactory;
    private $pagerFactory;
    private $tokenStorage;
    private $authorization;
    private $translator;
    private $utils;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "announcementManager" = @DI\Inject("claroline.announcement.manager.announcement_manager"),
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "translator"          = @DI\Inject("translator"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        AnnouncementManager $announcementManager,
        FormFactoryInterface $formFactory,
        PagerFactory $pagerFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        Utilities $utils,
        WorkspaceManager $workspaceManager
    ) {
        $this->announcementManager = $announcementManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->pagerFactory = $pagerFactory;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
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
        $collection = new ResourceCollection(array($aggregate->getResourceNode()));

        try {
            $this->checkAccess('EDIT', $aggregate);
            $announcements = $this->announcementManager->getAllAnnouncementsByAggregate($aggregate);
        } catch (AccessDeniedException $e) {
            $this->checkAccess('OPEN', $aggregate);
            $announcements = $this->announcementManager->getVisibleAnnouncementsByAggregate($aggregate);
        }
        $pager = $this->pagerFactory->createPagerFromArray($announcements, $page, 5);

        return array(
            '_resource' => $aggregate,
            'announcements' => $pager,
            'resourceCollection' => $collection,
        );
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

        return array(
            'form' => $form->createView(),
            'type' => 'create',
            '_resource' => $aggregate,
        );
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
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $visibleFrom = $announcement->getVisibleFrom();
            $visibleUntil = $announcement->getVisibleUntil();

            if (!is_null($visibleFrom) && !is_null($visibleUntil) && $visibleUntil <= $visibleFrom) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->translator->trans(
                        'visible_from_until_condition',
                        array(),
                        'announcement'
                    )
                );

                return array(
                    'form' => $form->createView(),
                    'type' => 'create',
                    '_resource' => $aggregate,
                );
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
                    array('aggregateId' => $aggregate->getId())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'type' => 'create',
            '_resource' => $aggregate,
        );
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

        return array(
            'form' => $form->createView(),
            'type' => 'edit',
            'announcement' => $announcement,
            '_resource' => $resource,
        );
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

        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $visibleFrom = $announcement->getVisibleFrom();
            $visibleUntil = $announcement->getVisibleUntil();

            if (!is_null($visibleFrom) && !is_null($visibleUntil) && $visibleUntil <= $visibleFrom) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->translator->trans('visible_from_until_condition', array(), 'announcement')
                );

                return array(
                    'form' => $form->createView(),
                    'type' => 'edit',
                    'announcement' => $announcement,
                    '_resource' => $resource,
                );
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
                    array('aggregateId' => $resource->getId())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'type' => 'edit',
            'announcement' => $announcement,
            '_resource' => $resource,
        );
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
     *     "/announcements/workspace/{workspaceId}/page/{page}",
     *     name="claro_workspace_announcements_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsWorkspaceWidgetPager.html.twig")
     *
     * Renders announcements in a pager.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param $page
     *
     * @return Response
     */
    public function announcementsWorkspaceWidgetPagerAction(Workspace $workspace, $page)
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);
        $data = $this->announcementManager->getVisibleAnnouncementsByWorkspace($workspace, $roles);
        $pager = $this->pagerFactory->createPagerFromArray($data, $page, 5);

        return array(
            'datas' => $pager,
            'widgetType' => 'workspace',
            'workspaceId' => $workspace->getId(),
        );
    }

    /**
     * @EXT\Route(
     *     "/announcements/page/{page}",
     *     name="claro_desktop_announcements_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsDesktopWidgetPager.html.twig")
     *
     * Renders announcements in a pager.
     *
     * @param $page
     *
     * @return Response
     */
    public function announcementsDesktopWidgetPagerAction($page)
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);
        $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);
        $data = $this->announcementManager->getVisibleAnnouncementsByWorkspaces($workspaces, $roles);
        $pager = $this->pagerFactory->createPagerFromArray($data, $page, 5);

        return array('datas' => $pager, 'widgetType' => 'desktop');
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
        $collection = new ResourceCollection(array($resource->getResourceNode()));

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
