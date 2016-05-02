<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormError;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class AuthenticationController extends Controller
{
    /** @DI\Inject("claroline.config.platform_config_handler") */
    private $configHandler;
    /** @DI\Inject("form.factory") */
    private $formFactory;
    /** @DI\Inject("request") */
    private $request;

    /**
     * @EXT\Route("/external/[[external_authentication]]/form", name="[[vendor]]_[[external_authentication]]_form")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authenticationConfigureFormAction()
    {
        //form creation
    }

    /**
     * @EXT\Route("/external/[[external_authentication]]/submit", name="[[vendor]]_[[external_authentication]]_submit")
     * @EXT\Method("POST")
     * @EXT\Template
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAuthenticationConfigureFormAction()
    {
        //form submission
    }
}
