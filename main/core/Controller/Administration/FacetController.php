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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

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
        return [];
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

        return [
            'facets' => $facets,
            'platformRoles' => $platformRoles,
            'profilePreferences' => $profilePreferences,
        ];
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

        return [
            'platformRoles' => $platformRoles,
            'labels' => $labels,
            'properties' => $properties,
        ];
    }
}
