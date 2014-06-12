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
use Claroline\CoreBundle\Manager\FacetManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Form\Administration\FacetType;
use Claroline\CoreBundle\Form\Administration\FieldFacetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FacetController extends Controller
{
    private $router;
    private $toolManager;
    private $userAdminTool;
    private $facetManager;

    /**
     * @DI\InjectParams({
     *     "router"       = @DI\Inject("router"),
     *     "sc"           = @DI\Inject("security.context"),
     *     "toolManager"  = @DI\Inject("claroline.manager.tool_manager"),
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
            $this->facetManager->createFacet($form->get('name')->getData());

            return new Response('success', 204);
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
            $this->facetManager->addField(
                $facet,
                $form->get('name')->getData(),
                $form->get('type')->getData()
            );

            return new Response('success');
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
        $form = $this->formFactory->create(new FacetType(), $facet);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->facetManager->editFacet($facet, $form->get('name')->getData());

            return new Response('success', 204);
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
            $this->facetManager->editField(
                $fieldFacet,
                $form->get('name')->getData(),
                $form->get('type')->getData()
            );

            return new Response('success');
        }

        return $this->render(
            'ClarolineCoreBundle:Administration\Facet:editFieldFacetForm.html.twig',
            array('form' => $form->createView(), 'fieldFacet' => $fieldFacet)
        );
    }

    /**
     * Ajax method for ordering fields
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


    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->userAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
} 