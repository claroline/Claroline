<?php

namespace Icap\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\OAuthBundle\Form\FacebookType;
use Symfony\Component\Form\FormError;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class FacebookController extends Controller
{
    /** @DI\Inject("claroline.config.platform_config_handler") */
    private $configHandler;
    /** @DI\Inject("form.factory") */
    private $formFactory;
    /** @DI\Inject("request") */
    private $request;
    /** @DI\Inject("claroline.manager.cache_manager") */
    private $cacheManager;
    /** @DI\Inject("icap.oauth.manager.facebook") */
    private $facebookManager;
    /** @DI\Inject("translator") */
    private $translator;

    /**
     * @EXT\Route("/oauth/facebook", name="claro_admin_facebook_form")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function facebookFormAction()
    {
        $config = $this->facebookManager->getConfiguration();
        $form = $this->formFactory->create(new FacebookType(), $config);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/oauth/facebook/submit", name="claro_admin_facebook_form_submit")
     * @EXT\Method("POST")
     * @EXT\Template("IcapOAuthBundle:Facebook:facebookForm.html.twig")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitFacebookFormAction()
    {
        $config = $this->facebookManager->getConfiguration();
        $form = $this->formFactory->create(new FacebookType(), $config);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = array(
                'facebook_client_id' => $form['facebook_client_id']->getData(),
                'facebook_client_secret' => $form['facebook_client_secret']->getData(),
                'facebook_client_active' => $form['facebook_client_active']->getData()
            );

            $errors = $this->facebookManager->validateFacebook(
                $data['facebook_client_id'], $data['facebook_client_secret']
            );

            if (count($errors) === 0) {
                $this->configHandler->setParameters($data);
                $this->cacheManager->refresh();
            } else {
                foreach ($errors as $error) {
                    $trans = $this->translator->trans($error, array(), 'platform');
                    $form->addError(new FormError($trans));
                }
            }
        }

        return array('form' => $form->createView());
    }
}
