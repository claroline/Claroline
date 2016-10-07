<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\User;

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\ProfileProperty;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Request;

/**
 * @NamePrefix("api_")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
 */
class FacetController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager")
     * })
     */
    public function __construct(
        FacetManager $facetManager,
        Request $request,
        ObjectManager $om,
                ProfilePropertyManager $profilePropertyManager
    ) {
        $this->facetManager = $facetManager;
        $this->request = $request;
        $this->om = $om;
        $this->profilePropertyManager = $profilePropertyManager;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     */
    public function getFacetsAction()
    {
        return $this->facetManager->getFacets();
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Post("facet/create", name="post_facet", options={ "method_prefix" = false })
     */
    public function createFacetAction()
    {
        $facet = $this->request->request->get('facet');
        $forceCreationForm = isset($facet['force_creation_form']) ? $facet['force_creation_form'] : false;
        $isMain = isset($facet['is_main']) ? $facet['is_main'] : false;

        return $this->facetManager->createFacet($facet['name'], $forceCreationForm, $isMain);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/{facet}", name="put_facet", options={ "method_prefix" = false })
     */
    public function editFacetAction(Facet $facet)
    {
        $data = $this->request->request->get('facet');
        $forceCreationForm = isset($data['force_creation_form']) ? $data['force_creation_form'] : false;
        $isMain = isset($data['is_main']) ? $data['is_main'] : false;

        return $this->facetManager->editFacet($facet, $data['name'], $forceCreationForm, $isMain);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Delete("facet/{facet}", name="delete_facet", options={ "method_prefix" = false })
     */
    public function deleteFacetAction(Facet $facet)
    {
        $this->facetManager->removeFacet($facet);

        return [];
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/{facet}/roles", name="put_facet_roles", options={ "method_prefix" = false })
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds" = true}
     * )
     */
    public function setFacetRolesAction(Facet $facet, array $roles)
    {
        return $this->facetManager->setFacetRoles($facet, $roles);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Post("facet/{facet}/panel/create", name="post_panel_facet", options={ "method_prefix" = false })
     */
    public function createFacetPanelAction(Facet $facet)
    {
        $panel = $this->request->request->get('panel');
        $collapse = isset($panel['is_default_collapsed']) ? $panel['is_default_collapsed'] : false;

        return $this->facetManager->addPanel($facet, $panel['name'], $collapse);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Post("facet/panel/field/{field}/choice", name="post_facet_field_choice", options={ "method_prefix" = false })
     */
    public function createFieldOptionsAction(FieldFacet $field)
    {
        $option = $this->request->request->get('choice');
        //there should be a facet validation here

        return $this->facetManager->addFacetFieldChoice($option['label'], $field);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Delete("facet/field/choice/{choice}", name="delete_facet_field_choice", options={ "method_prefix" = false })
     */
    public function deleteFieldOptionsAction(FieldFacetChoice $choice)
    {
        $this->facetManager->removeFieldFacetChoice($choice);

        return [];
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/panel/{panel}", name="put_panel_facet", options={ "method_prefix" = false })
     */
    public function editFacetPanelAction(PanelFacet $panel)
    {
        $data = $this->request->request->get('panel');
        $collapse = isset($data['is_default_collapsed']) ? $data['is_default_collapsed'] : false;

        return $this->facetManager->editPanel($panel, $data['name'], $collapse);
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Delete("facet/panel/{panel}", name="delete_panel_facet", options={ "method_prefix" = false })
     */
    public function deletePanelFacetAction(PanelFacet $panel)
    {
        $this->facetManager->removePanel($panel);

        return [];
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Post("facet/panel/{panel}/field/create", name="post_field_facet", options={ "method_prefix" = false })
     */
    public function createFieldFacetAction(PanelFacet $panel)
    {
        $field = $this->request->request->get('field');
        $isRequired = isset($field['is_required']) && $field['is_required'] === 'true';

        $fiendEntity = $this->facetManager->addField(
            $panel,
            $field['name'],
            $isRequired,
            $field['type']
        );

        if (isset($field['field_facet_choices'])) {
            foreach ($field['field_facet_choices'] as $choice) {
                $this->facetManager->addFacetFieldChoice($choice['label'], $fiendEntity);
            }
        }

        return $fiendEntity;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/panel/field/{field}", name="put_field_facet", options={ "method_prefix" = false })
     */
    public function editFieldFacetAction(FieldFacet $field)
    {
        $data = $this->request->request->get('field');
        $isRequired = isset($data['is_required']) && $data['is_required'] === 'true';
        $this->om->startFlushSuite();

        if (isset($data['field_facet_choices'])) {
            foreach ($data['field_facet_choices'] as $position => $choice) {
                $this->facetManager->editFacetFieldChoice($choice, $field, $position + 1);
            }
        }

        $field = $this->facetManager->editField($field, $data['name'], $isRequired, $data['type']);
        $this->om->endFlushSuite();

        return $field;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Delete("facet/panel/field/{field}", name="delete_field_facet", options={ "method_prefix" = false })
     */
    public function deleteFieldFacetAction(FieldFacet $field)
    {
        $this->facetManager->removeField($field);

        return [];
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/panel/{panel}/roles", name="put_panel_roles", options={ "method_prefix" = false })
     */
    public function putPanelRoleAction(PanelFacet $panel)
    {
        $params = $this->request->request->all();
        $this->om->startFlushSuite();

        foreach ($params['roles'] as $param) {
            $role = $this->om->getRepository('ClarolineCoreBundle:Role')->find($param['role']['id']);
            $canOpen = $param['can_open'] === 'true';
            $canEdit = $param['can_edit'] === 'true';
            $this->facetManager->setPanelFacetRole($panel, $role, $canOpen, $canEdit);
        }

        $isEditable = $params['is_editable'] === 'true';
        $this->facetManager->setPanelEditable($panel, $isEditable);
        $this->om->endFlushSuite();

        return $panel;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Get("facet/profile/preferences", name="get_profile_preferences", options={ "method_prefix" = false })
     */
    public function getProfilePreferencesAction()
    {
        return $this->facetManager->getProfilePreferences();
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/profile/preferences", name="put_profile_preferences", options={ "method_prefix" = false })
     */
    public function putProfilePreferencesAction()
    {
        $params = $this->request->request->all();
        $this->om->startFlushSuite();

        foreach ($params['preferences'] as $param) {
            $role = $this->om->getRepository('ClarolineCoreBundle:Role')->find($param['role']['id']);
            $baseData = $param['base_data'] === 'true';
            $mail = $param['mail'] === 'true';
            $phone = $param['phone'] === 'true';
            //old param. Not used anymore but it could be used again "soon"
            $sendMail = false;
            $sendMessage = $param['send_message'] === 'true';
            $this->facetManager->setProfilePreference($baseData, $mail, $phone, $sendMail, $sendMessage, $role);
        }

        $this->om->endFlushSuite();

        return $this->facetManager->getProfilePreferences();
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/{facet}/panels/order", name="put_panels_order", options={ "method_prefix" = false })
     */
    public function orderPanelsAction(Facet $facet)
    {
        $ids = $this->request->query->get('ids');
        $this->facetManager->orderPanels($ids, $facet);

        return $facet;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("facet/panel/{panel}/fields/order", name="put_fields_order", options={ "method_prefix" = false })
     */
    public function orderFieldsAction(PanelFacet $panel)
    {
        $ids = $this->request->query->get('ids');
        $this->facetManager->orderFields($ids, $panel);

        return $panel;
    }

    /**
     * @GET("/property/{property}/invert", name="post_invert_user_properties_edition", options = {"expose"=true, "method_prefix" = false})
     */
    public function invertPropertiesEditableAction(ProfileProperty $property)
    {
        $this->profilePropertyManager->invertProperty($property);

        return true;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("/facet/{facet}/down", name="move_facet_down", options={ "method_prefix" = false })
     */
    public function moveFacetDownAction(Facet $facet)
    {
        $this->facetManager->moveFacetDown($facet);

        return $facet;
    }

    /**
     * @View(serializerGroups={"api_facet_admin"})
     * @Put("/facet/{facet}/up", name="move_facet_up", options={ "method_prefix" = false })
     */
    public function moveFacetUpAction(Facet $facet)
    {
        $this->facetManager->moveFacetUp($facet);

        return $facet;
    }
}
