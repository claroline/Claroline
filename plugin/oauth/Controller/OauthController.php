<?php

namespace Icap\OAuthBundle\Controller;

use Icap\OAuthBundle\Form\ConfigurationType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
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
    /** @DI\Inject("claroline.manager.cache_manager") */
    private $cacheManager;
    /** @DI\Inject("icap.oauth.manager") */
    private $oauthManager;
    /** @DI\Inject("translator") */
    private $translator;

    /**
     * @EXT\Route("/admin/parameters/oauth/{service}", name="claro_admin_oauth_form")
     * @EXT\Template("IcapOAuthBundle::admin_form.html.twig")
     *
     * @param $service
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction($service)
    {
        $config = $this->oauthManager->getConfiguration($service);
        $form = $this->formFactory->create(new ConfigurationType(), $config, ['resource_owner' => $service]);

        return ['form' => $form->createView(), 'service' => $service];
    }

    /**
     * @EXT\Route("/admin/parameters/oauth/{service}/submit", name="claro_admin_oauth_form_submit")
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
        $form = $this->formFactory->create(new ConfigurationType(), $config, ['resource_owner' => $service]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = [
                $service.'_client_id' => $form['client_id']->getData(),
                $service.'_client_secret' => $form['client_secret']->getData(),
                $service.'_client_active' => $form['client_active']->getData(),
            ];

            if ($service !== 'linkedin') {
                $data[$service.'_client_force_reauthenticate'] = $form['client_force_reauthenticate']->getData();
            }

            if ($service === 'office_365') {
                $clientTenandDomain = $form['client_tenant_domain']->getData();
                $data[$service.'_client_domain'] = $clientTenandDomain === null ? '' : $clientTenandDomain;
            }

            if ($service === 'generic') {
                $data[$service.'_authorization_url'] = $form['authorization_url']->getData();
                $data[$service.'_access_token_url'] = $form['access_token_url']->getData();
                $data[$service.'_infos_url'] = $form['infos_url']->getData();
                $data[$service.'_scope'] = $form['scope']->getData();
                $data[$service.'_paths_login'] = $form['paths_login']->getData();
                $data[$service.'_paths_email'] = $form['paths_email']->getData();
                $data[$service.'_display_name'] = $form['display_name']->getData();
            }

            $errors = $this->oauthManager->validateService(
                $service,
                $data[$service.'_client_id'],
                $data[$service.'_client_secret']
            );

            if (count($errors) === 0) {
                $this->configHandler->setParameters($data);
                $this->cacheManager->refresh();

                return $this->redirectToRoute('claro_admin_parameters_third_party_authentication_index');
            } else {
                foreach ($errors as $error) {
                    $trans = $this->translator->trans($error, [], 'platform');
                    $form->addError(new FormError($trans));
                }
            }
        }

        return ['form' => $form->createView(), 'service' => $service];
    }
}
