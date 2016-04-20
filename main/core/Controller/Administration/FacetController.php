<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\ProfileProperty;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Form\Administration\FacetType;
use Claroline\CoreBundle\Form\Administration\FieldFacetType;
use Claroline\CoreBundle\Form\Administration\PanelFacetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
 */
class FacetController extends Controller
{
    private $router;
    private $roleManager;
    private $facetManager;
    private $profilePropertyManager;

    /**
     * @DI\InjectParams({
     *     "router"                 = @DI\Inject("router"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "request"                = @DI\Inject("request"),
     *     "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager")
     * })
     */
    public function __construct(
        RouterInterface $router,
        FacetManager $facetManager,
        RoleManager $roleManager,
        FormFactoryInterface $formFactory,
        Request $request,
        ProfilePropertyManager $profilePropertyManager
    ) {
        $this->facetManager = $facetManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->profilePropertyManager = $profilePropertyManager;
    }

    /**
     * Returns the facet list.
     *
     * @EXT\Route("/index", name="claro_admin_facet_index")
     * @EXT\Template
     *
     * @return Response
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns the facet list.
     *
     * @EXT\Route("/facet", name="claro_admin_facet")
     * @EXT\Template
     *
     * @return Response
     */
    public function facetsAction()
    {
        $facets = $this->facetManager->getFacets();
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles(true);
        $profilePreferences = $this->facetManager->getProfilePreferences();

        return array(
            'facets' => $facets,
            'platformRoles' => $platformRoles,
            'profilePreferences' => $profilePreferences,
        );
    }

    /**
     * Returns the facet list.
     *
     * @EXT\Route("/properties", name="claro_admin_profile_properties")
     * @EXT\Template
     *
     * @return Response
     */
    public function profilePropertiesAction()
    {
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles(false);
        $labels = User::getEditableProperties();
        $properties = $this->profilePropertyManager->getAllProperties();

        return array(
            'platformRoles' => $platformRoles,
            'labels' => $labels,
            'properties' => $properties,
        );
    }

    /**
     * @EXT\Route("/property/{property}/invert",
     *      name="claro_admin_invert_user_properties_edition",
     *      options = {"expose"=true}
     * )
     */
    public function invertPropertiesEditableAction(ProfileProperty $property)
    {
        $this->profilePropertyManager->invertProperty($property);

        return new JsonResponse(array(), 200);
    }

    /**
     * Returns the facet creation form in a modal.
     *
     * @EXT\Route("/form",
     *      name="claro_admin_facet_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template
     */
    public function facetFormAction()
    {
        $form = $this->formFactory->create(new FacetType(), new Facet());

        return array('form' => $form->createView());
    }

    /**
     * Returns the facet field creation form in a modal.
     *
     * @EXT\Route("/panel/{panelFacet}/field/form",
     *      name="claro_admin_facet_field_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template
     */
    public function fieldFormAction(PanelFacet $panelFacet)
    {
        $form = $this->formFactory->create(new FieldFacetType(), new FieldFacet());

        return array('form' => $form->createView(), 'panelFacet' => $panelFacet);
    }

    /**
     * Returns the facet creation form in a modal.
     *
     * @EXT\Route("/create",
     *      name="claro_admin_facet_create",
     *      options = {"expose"=true}
     * )
     */
    public function createFacetAction()
    {
        $form = $this->formFactory->create(new FacetType(), new Facet());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $facet = $this->facetManager->createFacet($form->get('name')->getData(), $form->get('forceCreationForm')->getData());

            return new JsonResponse(
                array('name' => $facet->getName(), 'position' => $facet->getPosition(), 'id' => $facet->getId())
            );
        }

        return $this->render(
           'ClarolineCoreBundle:Administration\Facet:facetForm.html.twig',
           array('form' => $form->createView())
       );
    }

    /**
     * Returns the facet creation form in a modal.
     *
     * @EXT\Route("/create/field/panel/{panelFacet}",
     *      name="claro_admin_field_facet_create",
     *      options = {"expose"=true}
     * )
     */
    public function createFieldAction(PanelFacet $panelFacet)
    {
        $form = $this->formFactory->create(new FieldFacetType(), new FieldFacet());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $field = $this->facetManager->addField(
                $panelFacet,
                $form->get('name')->getData(),
                $form->get('type')->getData()
            );

            return new JsonResponse(
                array(
                    'name' => $field->getName(),
                    'position' => $field->getPosition(),
                    'typeTranslationKey' => $field->getTypeTranslationKey(),
                    'id' => $field->getId(),
                    'panelId' => $panelFacet->getId(),
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:fieldForm.html.twig',
            array('form' => $form->createView(), 'panelId' => $panelFacet)
        );
    }

    /**
     * Removes a facet.
     *
     * @EXT\Route("/{facet}/remove",
     *      name="claro_admin_facet_remove",
     *      options = {"expose"=true}
     * )
     */
    public function removeFacetAction(Facet $facet)
    {
        $this->facetManager->removeFacet($facet);

        return new Response('success');
    }

    /**
     * Returns the facet form edition in a modal.
     *
     * @EXT\Route("/{facet}/edit/form",
     *      name="claro_admin_facet_edit_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template()
     */
    public function editFacetFormAction(Facet $facet)
    {
        $form = $this->formFactory->create(new FacetType(), $facet);

        return array('form' => $form->createView(), 'facet' => $facet);
    }

    /**
     * Returns the facet form edition in a modal.
     *
     * @EXT\Route("/{facet}/edit",
     *      name="claro_admin_facet_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editFacetAction(Facet $facet)
    {
        $oldName = $facet->getName();
        $form = $this->formFactory->create(new FacetType(), $facet);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $facet = $this->facetManager->editFacet($facet, $form->get('name')->getData(), $form->get('forceCreationForm')->getData());

            return new JsonResponse(
                array(
                    'id' => $facet->getId(),
                    'name' => $facet->getName(),
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editFacetForm.html.twig',
            array('form' => $form->createView(), 'facet' => $facet)
        );
    }

    /**
     * Ajax method for removing a field facet.
     *
     * @EXT\Route("/field/remove/{fieldFacet}",
     *      name="claro_admin_remove_field_facet",
     *      options = {"expose"=true}
     * )
     */
    public function removeFieldFacetAction(FieldFacet $fieldFacet)
    {
        ;
        $this->facetManager->removeField($fieldFacet);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route("/{facet}/up",
     *      name="claro_admin_move_facet_up",
     *      options = {"expose"=true}
     * )
     */
    public function moveFacetUpAction(Facet $facet)
    {
        $this->facetManager->moveFacetUp($facet);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route("/{facet}/down",
     *      name="claro_admin_move_facet_down",
     *      options = {"expose"=true}
     * )
     */
    public function moveFacetDownAction(Facet $facet)
    {
        $this->facetManager->moveFacetDown($facet);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route("/field/{fieldFacet}/edit/form",
     *      name="claro_admin_field_facet_edit_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template()
     */
    public function editFieldFormAction(FieldFacet $fieldFacet)
    {
        $form = $this->formFactory->create(new FieldFacetType(), $fieldFacet);

        return array('form' => $form->createView(), 'fieldFacet' => $fieldFacet);
    }

    /**
     * @EXT\Route("/field/{fieldFacet}/edit",
     *      name="claro_admin_field_facet_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editFieldAction(FieldFacet $fieldFacet)
    {
        $form = $this->formFactory->create(new FieldFacetType(), $fieldFacet);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $fieldFacet = $this->facetManager->editField(
                $fieldFacet,
                $form->get('name')->getData(),
                $form->get('type')->getData()
            );

            return new JsonResponse(
                array(
                    'id' => $fieldFacet->getId(),
                    'name' => $fieldFacet->getName(),
                    'typeTranslationKey' => $fieldFacet->getTypeTranslationKey(),
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editFieldFacetForm.html.twig',
            array('form' => $form->createView(), 'fieldFacet' => $fieldFacet)
        );
    }

    /**
     * Ajax method for ordering fields.
     *
     * @EXT\Route("/{panel}/fields/order",
     *      name="claro_admin_field_facet_order",
     *      options = {"expose"=true}
     * )
     */
    public function moveFieldFacetsAction(PanelFacet $panel)
    {
        $params = $this->request->query->all();
        $ids = [];

        foreach ($params['ids'] as $value) {
            $ids[] = (int) str_replace('field-', '', $value);
        }

        $this->facetManager->orderFields($ids, $panel);

        return new Response('success');
    }

    /**
     * Returns the facet role edition in a modal.
     *
     * @EXT\Route("/{facet}/roles/form",
     *      name="claro_admin_facet_role_form",
     *      options = {"expose"=true}
     * )
     *
     * @EXT\Template()
     */
    public function facetRolesFormAction(Facet $facet)
    {
        $roles = $facet->getRoles();
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles(true);

        return array('roles' => $roles, 'facet' => $facet, 'platformRoles' => $platformRoles);
    }

    /**
     * @EXT\Route("/{facet}/roles/edit",
     *      name="claro_admin_facet_role_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editFacetRolesAction(Facet $facet)
    {
        $roles = $this->getRolesFromRequest('role-');
        $this->facetManager->setFacetRoles($facet, $roles);

        return new JsonResponse(array(), 204);
    }

    /**
     * Returns the field role edition in a modal.
     *
     * @EXT\Route("/field/{field}/roles/form",
     *      name="claro_admin_field_role_form",
     *      options = {"expose"=true}
     * )
     *
     * @EXT\Template()
     */
    public function fieldRolesFormAction(FieldFacet $field)
    {
        $fieldFacetsRole = $field->getFieldFacetsRole();
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles(true);

        return array('fieldFacetsRole' => $fieldFacetsRole, 'field' => $field, 'platformRoles' => $platformRoles);
    }

    /**
     * @EXT\Route("/field/{field}/roles/edit",
     *      name="claro_admin_field_role_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editFieldRolesAction(FieldFacet $field)
    {
        $roles = $this->getRolesFromRequest('open-role-');
        $this->facetManager->setFieldBoolProperty($field, $roles, 'canOpen');
        $roles = $this->getRolesFromRequest('edit-role-');
        $this->facetManager->setFieldBoolProperty($field, $roles, 'canEdit');

        return new JsonResponse(array(), 204);
    }

    /**
     * @EXT\Route("/edit/general",
     *      name="claro_admin_facet_general_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editGeneralFacet()
    {
        $configs = array();

        foreach ($this->request->request->all() as $key => $value) {
            $arr = explode('-role-', $key);
            $roleId = (int) $arr[1];
            $configs[$roleId][$arr[0]] = true;
        }

        foreach ($configs as $key => $config) {
            $this->facetManager->setProfilePreference(
                isset($config['basedata']),
                isset($config['mail']),
                isset($config['phone']),
                isset($config['sendmail']),
                isset($config['sendmessage']),
                $this->roleManager->getRole($key)
            );
        }

        return new JsonResponse($this->request->request->all(), 200);
    }

    /**
     * Returns the panel creation form in a modal.
     *
     * @EXT\Route("/create/panel/facet/{facet}/form",
     *      name="claro_admin_panel_facet_create_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template()
     */
    public function panelFacetFormAction(Facet $facet)
    {
        $form = $this->formFactory->create(new PanelFacetType(), new PanelFacet());

        return array('form' => $form->createView(), 'facet' => $facet);
    }

    /**
     * Returns the panel creation form in a modal.
     *
     * @EXT\Route("/create/panel/facet/{facet}",
     *      name="claro_admin_panel_facet_create",
     *      options = {"expose"=true}
     * )
     */
    public function addPanelFacetAction(Facet $facet)
    {
        $form = $this->formFactory->create(new PanelFacetType(), new PanelFacet());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $panel = $this->facetManager->addPanel(
                $facet,
                $form->get('name')->getData(),
                $form->get('isDefaultCollapsed')->getData()
            );

            return new JsonResponse(
                array(
                    'id' => $panel->getId(),
                    'name' => $panel->getName(),
                    'facet_id' => $facet->getId(),
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:panelFacetForm.html.twig',
            array('form' => $form->createView(), 'facet' => $facet)
        );
    }

    /**
     * Returns the panel creation edition in a modal.
     *
     * @EXT\Route("/edit/panel/facet/{panelFacet}/form",
     *      name="claro_admin_panel_facet_edit_form",
     *      options = {"expose"=true}
     * )
     *
     * @EXT\Template()
     */
    public function editPanelFacetFormAction(PanelFacet $panelFacet)
    {
        $form = $this->formFactory->create(new PanelFacetType(), $panelFacet);

        return array('form' => $form->createView(), 'panelFacet' => $panelFacet);
    }

    /**
     * Returns the panel creation edition in a modal.
     *
     * @EXT\Route("/edit/panel/facet/{panelFacet}",
     *      name="claro_admin_panel_facet_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editPanelFacetAction(PanelFacet $panelFacet)
    {
        $form = $this->formFactory->create(new PanelFacetType(), $panelFacet);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $panel = $this->facetManager->editPanel($panelFacet);

            return new JsonResponse(
                array(
                    'id' => $panelFacet->getId(),
                    'name' => $panelFacet->getName(),
                    'facet_id' => $panelFacet->getId(),
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editPanelFacetForm.html.twig',
            array('form' => $form->createView(), 'panelFacet' => $panelFacet)
        );
    }

    /**
     * Removes a panel.
     *
     * @EXT\Route("/remove/panel/facet/{panelFacet}",
     *      name="claro_admin_remove_panel_facet",
     *      options = {"expose"=true}
     * )
     */
    public function removePanelFacetAction(PanelFacet $panelFacet)
    {
        $this->facetManager->removePanel($panelFacet);

        return new Response('success', 204);
    }

    /**
     * Reorder panels.
     *
     * @EXT\Route("/order/panels/facet/{facet}",
     *      name="claro_admin_panel_facet_order",
     *      options = {"expose" = true}
     * )
     */
    public function orderPanels(Facet $facet)
    {
        $params = $this->request->query->all();
        $ids = [];

        foreach ($params['ids'] as $value) {
            $ids[] = (int) str_replace('panel-', '', $value);
        }

        $this->facetManager->orderPanels($ids, $facet);

        return new Response('success');
    }

    private function getRolesFromRequest($prefix)
    {
        $params = $this->request->request->all();
        $roleIds = [];

        foreach ($params as $key => $value) {
            $key = '_'.$key;
            if (strpos($key, $prefix)) {
                if ('on' === $value) {
                    $roleIds[] = (int) str_replace('_'.$prefix, '', $key);
                }
            }
        }

        $roles = $this->roleManager->getRolesByIds($roleIds);

        return $roles;
    }
}
