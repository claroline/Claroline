<?php

namespace FormaLibre\OfficeConnectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use FormaLibre\OfficeConnectBundle\Form\OfficeConnectType;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    /** @DI\Inject("router") */
    private $router;
    /** @DI\Inject("formalibre.office_connect.library.settings") */
    private $settings;

    /**
     * @EXT\Route("/external/office/form", name="formalibre_office_form")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authenticationConfigureFormAction()
    {
        $config = $this->settings->getConfiguration();
        $form = $this->formFactory->create(new OfficeConnectType(), $config);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/external/office/submit", name="formalibre_office_submit")
     * @EXT\Method("POST")
     * @EXT\Template("FormaLibreOfficeConnectBundle:Authentication:authenticationConfigureForm.html.twig")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAuthenticationConfigureFormAction()
    {
        $config = $this->settings->getConfiguration();
        $form = $this->formFactory->create(new OfficeConnectType(), $config);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = array(
                'o365_client_id' => $form['office_client_id']->getData(),
                'o365_pw' => $form['office_password']->getData(),
                'o365_domain' => $form['office_app_tenant_domain_name']->getData(),
                'o365_active' => $form['office_client_active']->getData(),
            );

            $this->configHandler->setParameters($data);

            return new RedirectResponse($this->router->generate('claro_admin_parameters_oauth_index'));
        }

        return array('form' => $form->createView());
    }
}
