<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AdministrationHomeTabController extends Controller
{
    private $formFactory;
    private $homeTabManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HomeTabManager $homeTabManager
    )
    {
        $this->formFactory = $formFactory;
        $this->homeTabManager = $homeTabManager;
    }

    /**
     * @EXT\Route(
     *     "/home_tabs/configuration",
     *     name="claro_admin_home_tabs_configuration",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminHomeTabsConfiguration.html.twig")
     *
     * Displays the admin homeTabs configuration page.
     *
     * @return Response
     */
    public function adminHomeTabsConfigurationAction()
    {
        $desktopHomeTabs = $this->homeTabManager->getAdminDesktopHomeTabs();
        $workspaceHomeTabs = $this->homeTabManager->getAdminWorkspaceHomeTabs();

        return array(
            'desktopHomeTabs' => $desktopHomeTabs,
            'workspaceHomeTabs' => $workspaceHomeTabs
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create/form",
     *     name="claro_admin_desktop_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabCreateForm.html.twig")
     *
     * Displays the admin desktop homeTab form.
     *
     * @return Response
     */
    public function adminDesktopHomeTabCreateFormAction()
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/create",
     *     name="claro_admin_desktop_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabCreateForm.html.twig")
     *
     * Create a new admin desktop homeTab.
     *
     * @return Response
     */
    public function adminDesktopHomeTabCreateAction()
    {
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $homeTab->setType('desktop');
            $lastOrder = $this->homeTabManager->getOrderOfLastAdminDesktopHomeTab();

            if (is_null($lastOrder['order_max'])) {
                $homeTab->setTabOrder(1);
            }
            else {
                $homeTab->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/create/form",
     *     name="claro_admin_workspace_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabCreateForm.html.twig")
     *
     * Displays the admin workspace homeTab form.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabCreateFormAction()
    {
        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/create",
     *     name="claro_admin_workspace_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabCreateForm.html.twig")
     *
     * Create a new admin workspace homeTab.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabCreateAction()
    {
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $homeTab->setType('workspace');
            $lastOrder = $this->homeTabManager->getOrderOfLastAdminWorkspaceHomeTab();

            if (is_null($lastOrder['order_max'])) {
                $homeTab->setTabOrder(1);
            }
            else {
                $homeTab->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabId}/edit/form",
     *     name="claro_admin_desktop_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Displays the admin desktop homeTab edition form.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditFormAction(HomeTab $homeTab)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/{homeTabId}/edit/form",
     *     name="claro_admin_workspace_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Displays the admin workspace homeTab edition form.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditFormAction(HomeTab $homeTab)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabId}/{homeTabName}/edit",
     *     name="claro_admin_desktop_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Edit the admin desktop homeTab.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditAction(HomeTab $homeTab, $homeTabName)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/{homeTabId}/{homeTabName}/edit",
     *     name="claro_admin_workspace_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Edit the admin workspace homeTab.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditAction(HomeTab $homeTab, $homeTabName)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array(
            'form' => $form->createView(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/delete",
     *     name="claro_admin_home_tab_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     *
     * Delete the given homeTab.
     *
     * @return Response
     */
    public function adminHomeTabDeleteAction(HomeTab $homeTab)
    {
        $this->homeTabManager->deleteHomeTab($homeTab);

        return new Response('success', 204);
    }
}