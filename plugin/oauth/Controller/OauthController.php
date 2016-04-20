<?php

namespace Icap\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\OAuthBundle\Form\ConfigurationType;
use Symfony\Component\Form\FormError;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class OauthController extends Controller
{
    /** @DI\Inject("claroline.config.platform_config_handler") */
    private $configHandler;
    /** @DI\Inject("form.factory") */
    private $formFactory;
    /** @DI\Inject("request") */
    private $request;
    /** @DI\Inject("claroline.manager.cache_manager") */
    private $cacheManager;
    /** @DI\Inject("icap.oauth.manager") */
    private $oauthManager;
    /** @DI\Inject("translator") */
    private $translator;

    /**
     * @EXT\Route("/oauth/{service}", name="claro_admin_oauth_form")
     * @EXT\Template("IcapOAuthBundle::admin_form.html.twig")
     *
     * @param $service
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction($service)
    {
        $config = $this->oauthManager->getConfiguration($service);
        $form = $this->formFactory->create(new ConfigurationType(), $config);

        return array('form' => $form->createView(), 'service' => $service);
    }

    /**
     * @EXT\Route("/oauth/{service}/submit", name="claro_admin_oauth_form_submit")
     * @EXT\Method("POST")
     * @EXT\Template("IcapOAuthBundle::admin_form.html.twig")
     *
     * Displays the administration section index.
     *
     * @param Request $request
     * @param $service
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitFormAction(Request $request, $service)
    {
        $config = $this->oauthManager->getConfiguration($service);
        $form = $this->formFactory->create(new ConfigurationType(), $config);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = array(
                $service.'_client_id' => $form['client_id']->getData(),
                $service.'_client_secret' => $form['client_secret']->getData(),
                $service.'_client_active' => $form['client_active']->getData(),
            );

            $errors = $this->oauthManager->validateService(
                $service,
                $data[$service.'_client_id'],
                $data[$service.'_client_secret']
            );

            if (count($errors) === 0) {
                $this->configHandler->setParameters($data);
                $this->cacheManager->refresh();

                return $this->redirectToRoute('claro_admin_parameters_oauth_index');
            } else {
                foreach ($errors as $error) {
                    $trans = $this->translator->trans($error, array(), 'platform');
                    $form->addError(new FormError($trans));
                }
            }
        }

        return array('form' => $form->createView(), 'service' => $service);
    }
}
