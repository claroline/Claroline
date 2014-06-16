<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Form\Administration\FacetType;
use Claroline\CoreBundle\Form\Administration\FieldFacetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class FacetController extends Controller
{
    private $router;
    private $toolManager;
    private $roleManager;
    private $userAdminTool;
    private $facetManager;

    /**
     * @DI\InjectParams({
     *     "router"       = @DI\Inject("router"),
     *     "sc"           = @DI\Inject("security.context"),
     *     "toolManager"  = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager"),
     *     "facetManager" = @DI\Inject("claroline.manager.facet_manager"),
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "request"      = @DI\Inject("request")
     * })
     */
    public function __construct(
        RouterInterface $router,
        SecurityContextInterface $sc,
        ToolManager $toolManager,
        FacetManager $facetManager,
        RoleManager $roleManager,
        FormFactoryInterface $formFactory,
        Request $request
    )
    {
        $this->sc            = $sc;
        $this->toolManager   = $toolManager;
        $this->userAdminTool = $this->toolManager->getAdminToolByName('user_management');
        $this->facetManager  = $facetManager;
        $this->formFactory   = $formFactory;
        $this->request       = $request;
        $this->roleManager   = $roleManager;
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
        $this->checkOpen();
        $facets = $this->facetManager->getFacets();

        return array('facets' => $facets);
    }

    /**
     * Returns the facet creation form in a modal
     *
     * @EXT\Route("/form",
     *      name="claro_admin_facet_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template
     */
    public function facetFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new FacetType(), new Facet());

        return array('form' => $form->createView());
    }

    /**
     * Returns the facet field creation form in a modal
     *
     * @EXT\Route("{facet}/field/form",
     *      name="claro_admin_facet_field_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template
     */
    public function fieldFormAction(Facet $facet)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new FieldFacetType(), new FieldFacet());

        return array('form' => $form->createView(), 'facet' => $facet);
    }

    /**
     * Returns the facet creation form in a modal
     *
     * @EXT\Route("/create",
     *      name="claro_admin_facet_create",
     *      options = {"expose"=true}
     * )
     */
    public function createFacetAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new FacetType(), new Facet());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $facet = $this->facetManager->createFacet($form->get('name')->getData());

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
     * Returns the facet creation form in a modal
     *
     * @EXT\Route("/create/field/facet/{facet}",
     *      name="claro_admin_field_facet_create",
     *      options = {"expose"=true}
     * )
     */
    public function createFieldAction(Facet $facet)
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new FieldFacetType(), new FieldFacet());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $field = $this->facetManager->addField(
                $facet,
                $form->get('name')->getData(),
                $form->get('type')->getData()
            );

            return new JsonResponse(
                array(
                    'name' => $field->getName(),
                    'position' => $field->getPosition(),
                    'typeTranslationKey' => $field->getTypeTranslationKey(),
                    'id' => $field->getId(),
                    'facet_id' => $facet->getId()
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:fieldForm.html.twig',
            array('form' => $form->createView(), 'facet' => $facet)
        );
    }

    /**
     * Removes a facet
     *
     * @EXT\Route("/{facet}/remove",
     *      name="claro_admin_facet_remove",
     *      options = {"expose"=true}
     * )
     */
    public function removeFacetAction(Facet $facet)
    {
        $this->checkOpen();
        $this->facetManager->removeFacet($facet);

        return new Response('success');
    }

    /**
     * Returns the facet form edition in a modal
     *
     * @EXT\Route("/{facet}/edit/form",
     *      name="claro_admin_facet_edit_form",
     *      options = {"expose"=true}
     * )
     * @EXT\Template()
     */
    public function editFacetFormAction(Facet $facet)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new FacetType(), $facet);

        return array('form' => $form->createView(), 'facet' => $facet);
    }

    /**
     * Returns the facet form edition in a modal
     *
     * @EXT\Route("/{facet}/edit",
     *      name="claro_admin_facet_edit",
     *      options = {"expose"=true}
     * )
     */
    public function editFacetAction(Facet $facet)
    {
        $this->checkOpen();
        $oldName = $facet->getName();
        $form = $this->formFactory->create(new FacetType(), $facet);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $facet = $this->facetManager->editFacet($facet, $form->get('name')->getData());

            return new JsonResponse(
                array(
                    'id' => $facet->getId(),
                    'name' => $facet->getName()
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editFacetForm.html.twig',
            array('form' => $form->createView(), 'facet' => $facet)
        );
    }

    /**
     * Ajax method for removing a field facet
     *
     * @EXT\Route("/field/remove/{fieldFacet}",
     *      name="claro_admin_remove_field_facet",
     *      options = {"expose"=true}
     * )
     */
    public function removeFieldFacetAction(FieldFacet $fieldFacet)
    {
        $this->checkOpen();
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
        $this->checkOpen();
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
        $this->checkOpen();
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
                    'typeTranslationKey' => $fieldFacet->getTypeTranslationKey()
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editFieldFacetForm.html.twig',
            array('form' => $form->createView(), 'fieldFacet' => $fieldFacet)
        );
    }

    /**
     * Ajax method for ordering fields
     *
     * @EXT\Route("/{facet}/fields/order",
     *      name="claro_admin_field_facet_order",
     *      options = {"expose"=true}
     * )
     */
    public function moveFieldFacetsAction(Facet $facet)
    {
        $this->checkOpen();
        $params = $this->request->query->all();
        $ids = [];

        foreach ($params['ids'] as $value) {
            $ids[] = (int) str_replace('field-', '', $value);
        }

        $this->facetManager->orderFields($ids, $facet);

        return new Response('success');
    }

    /**
     * Returns the facet role edition in a modal
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
        $this->checkOpen();
        $roles = $facet->getRoles();
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles();

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
        $this->checkOpen();
        $roles = $this->getRolesFromRequest('role-');
        $this->facetManager->setFacetRoles($facet, $roles);

        return new JsonResponse(array(), 204);
    }

    /**
     * Returns the field role edition in a modal
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
        $this->checkOpen();
        $fieldFacetsRole = $field->getFieldFacetsRole();
        $platformRoles = $this->roleManager->getPlatformNonAdminRoles();

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
        $this->checkOpen();
        $roles = $this->getRolesFromRequest('open-role-');
        $this->facetManager->setFieldBoolProperty($field, $roles, 'canOpen');
        $roles = $this->getRolesFromRequest('edit-role-');
        $this->facetManager->setFieldBoolProperty($field, $roles, 'canEdit');

        return new JsonResponse(array(), 204);
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->userAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }

    private function getRolesFromRequest($prefix)
    {
        $params = $this->request->request->all();
        $roleIds = [];

        foreach ($params as $key => $value) {
            $key = '_' . $key;
            if (strpos($key, $prefix)) {
                if ('on' === $value) {
                    $roleIds[] = (int) str_replace('_' . $prefix, '', $key);
                }
            }
        }

        $roles = $this->roleManager->getRolesByIds($roleIds);

        return $roles;
    }
} 