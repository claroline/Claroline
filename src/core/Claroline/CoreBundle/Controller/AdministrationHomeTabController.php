<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
        $desktopHomeTabConfigs = $this->homeTabManager->getAdminDesktopHomeTabConfigs();
        $workspaceHomeTabConfigs = $this->homeTabManager->getAdminWorkspaceHomeTabConfigs();

        return array(
            'desktopHomeTabConfigs' => $desktopHomeTabConfigs,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs
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
            $homeTab->setType('admin_desktop');
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('admin_desktop');
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(false);
            $lastOrder = $this->homeTabManager->getOrderOfLastAdminDesktopHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

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
            $homeTab->setType('admin_workspace');
            $this->homeTabManager->insertHomeTab($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('admin_workspace');
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(false);
            $lastOrder = $this->homeTabManager->getOrderOfLastAdminWorkspaceHomeTabConfig();

            if (is_null($lastOrder['order_max'])) {
                $homeTabConfig->setTabOrder(1);
            }
            else {
                $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTabConfig($homeTabConfig);

            return $this->redirect(
                $this->generateUrl('claro_admin_home_tabs_configuration')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabConfigId}/edit/form",
     *     name="claro_admin_desktop_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Displays the admin desktop homeTab edition form.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditFormAction(HomeTabConfig $homeTabConfig)
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/{homeTabConfigId}/edit/form",
     *     name="claro_admin_workspace_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Displays the admin workspace homeTab edition form.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditFormAction(HomeTabConfig $homeTabConfig)
    {
        $homeTab = $homeTabConfig->getHomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/{homeTabConfigId}/{homeTabName}/edit",
     *     name="claro_admin_desktop_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminDesktopHomeTabEditForm.html.twig")
     *
     * Edit the admin desktop homeTab.
     *
     * @return Response
     */
    public function adminDesktopHomeTabEditAction(HomeTabConfig $homeTabConfig, $homeTabName)
    {
        $homeTab = $homeTabConfig->getHomeTab();
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
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/home_tab/{homeTabConfigId}/{homeTabName}/edit",
     *     name="claro_admin_workspace_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:adminWorkspaceHomeTabEditForm.html.twig")
     *
     * Edit the admin workspace homeTab.
     *
     * @return Response
     */
    public function adminWorkspaceHomeTabEditAction(HomeTabConfig $homeTabConfig, $homeTabName)
    {
        $homeTab = $homeTabConfig->getHomeTab();
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
            'homeTabConfig' => $homeTabConfig,
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabId}/{tabOrder}/delete",
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
    public function adminHomeTabDeleteAction(HomeTab $homeTab, $tabOrder)
    {
        if (is_null($homeTab->getUser()) && is_null($homeTab->getWorkspace())) {
            $type = 'admin_' . $homeTab->getType();
            $this->homeTabManager->deleteHomeTab($homeTab, $type, $tabOrder);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/visibility/{visible}/update",
     *     name="claro_admin_home_tab_update_visibility",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Configure visibility of an Home tab
     *
     * @return Response
     */
    public function adminHomeTabUpdateVisibilityAction(HomeTabConfig $homeTabConfig, $visible)
    {
        $isVisible = ($visible === 'visible') ? true : false;
        $this->homeTabManager->updateVisibility($homeTabConfig, $isVisible);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/home_tab/{homeTabConfigId}/lock/{locked}/update",
     *     name="claro_admin_home_tab_update_lock",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTabConfig",
     *     class="ClarolineCoreBundle:Home\HomeTabConfig",
     *     options={"id" = "homeTabConfigId", "strictId" = true}
     * )
     *
     * Configure lock of an Home tab
     *
     * @return Response
     */
    public function adminHomeTabUpdateLockAction(HomeTabConfig $homeTabConfig, $locked)
    {
        $isLocked = ($locked === 'locked') ? true : false;
        $this->homeTabManager->updateLock($homeTabConfig, $isLocked);

        return new Response('success', 204);
    }
}