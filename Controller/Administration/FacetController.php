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
     *     "facetManager" = @DI\Inject("claroline.manager.facet_manager")
     * })
     */
    public function __construct(
        RouterInterface $router,
        SecurityContextInterface $sc,
        ToolManager $toolManager,
        FacetManager $facetManager
    )
    {
        $this->sc            = $sc;
        $this->toolManager   = $toolManager;
        $this->userAdminTool = $this->toolManager->getAdminToolByName('user_management');
        $this->facetManager  = $facetManager;
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
     * Ajax method for creating a new facet
     */
    public function createFacetAction()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for removing a facet
     */
    public function removeFacetAction()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for edition a facet name
     */
    public function editFacetNameAction()
    {
        $this->checkOpen();
    }

    /**
     * Shows a facet with its field list
     */
    public function showFacetAction()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for creating a new facet field
     */
    public function createFieldFacetAction()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for creating a new facet field
     */
    public function removeFieldFacetAction()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for moving a facet up
     */
    public function moveFacetUp()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for moving a facet down
     */
    public function moveFacetDown()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for moving a field facet up
     */
    public function moveFieldFacetUp()
    {
        $this->checkOpen();
    }

    /**
     * Ajax method for moving a field facet down
     */
    public function moveFieldFacetDown()
    {
        $this->checkOpen();
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->userAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
} 