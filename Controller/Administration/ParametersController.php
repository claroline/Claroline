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

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Claroline\CoreBundle\Library\Session\DatabaseSessionValidator;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Library\Installation\Settings\MailingSettings;
use Claroline\CoreBundle\Library\Installation\Settings\MailingChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Form\Administration as AdminForm;
use Claroline\CoreBundle\Manager\CacheManager;
use Claroline\CoreBundle\Library\Installation\Refresher;
use Claroline\CoreBundle\Manager\HwiManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller of the platform parameters section.
 */
class ParametersController extends Controller
{
    private $configHandler;
    private $roleManager;
    private $formFactory;
    private $request;
    private $localeManager;
    private $translator;
    private $mailManager;
    private $contentManager;
    private $cacheManager;
    private $dbSessionValidator;
    private $refresher;
    private $hwiManager;
    private $sc;
    private $toolManager;
    private $paramAdminTool;

    /**
     * @DI\InjectParams({
     *     "configHandler"      = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "localeManager"      = @DI\Inject("claroline.common.locale_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "translator"         = @DI\Inject("translator"),
     *     "termsOfService"     = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "cacheManager"       = @DI\Inject("claroline.manager.cache_manager"),
     *     "contentManager"     = @DI\Inject("claroline.manager.content_manager"),
     *     "sessionValidator"   = @DI\Inject("claroline.session.database_validator"),
     *     "refresher"          = @DI\Inject("claroline.installation.refresher"),
     *     "hwiManager"         = @DI\Inject("claroline.manager.hwi_manager"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"                 = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        RoleManager $roleManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        Request $request,
        Translator $translator,
        TermsOfServiceManager $termsOfService,
        MailManager $mailManager,
        ContentManager $contentManager,
        CacheManager $cacheManager,
        DatabaseSessionValidator $sessionValidator,
        Refresher $refresher,
        HwiManager $hwiManager,
        ToolManager $toolManager,
        SecurityContextInterface $sc
    )
    {
        $this->configHandler      = $configHandler;
        $this->roleManager        = $roleManager;
        $this->formFactory        = $formFactory;
        $this->request            = $request;
        $this->termsOfService     = $termsOfService;
        $this->localeManager      = $localeManager;
        $this->translator         = $translator;
        $this->mailManager        = $mailManager;
        $this->contentManager     = $contentManager;
        $this->cacheManager       = $cacheManager;
        $this->dbSessionValidator = $sessionValidator;
        $this->refresher          = $refresher;
        $this->hwiManager         = $hwiManager;
        $this->sc                 = $sc;
        $this->toolManager        = $toolManager;
        $this->paramAdminTool     = $this->toolManager->getAdminToolByName('platform_parameters');
    }

    /**
     * @EXT\Route("/", name="claro_admin_parameters_index")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @EXT\Route("/general", name="claro_admin_parameters_general")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generalFormAction()
    {
        $this->checkOpen();

        $description = $this->contentManager->getTranslatedContent(array('type' => 'platformDescription'));
        $platformConfig = $this->configHandler->getPlatformConfig();
        $role = $this->roleManager->getRoleByName($platformConfig->getDefaultRole());
        $form = $this->formFactory->create(
            new AdminForm\GeneralType($this->localeManager->getAvailableLocales(), $role, $description),
            $platformConfig
        );

        return array(
            'form_settings' => $form->createView(),
            'logos' => $this->get('claroline.common.logo_service')->listLogos()
        );
    }

    /**
     * @EXT\Route("/general", name="claro_admin_edit_parameters_general")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:generalForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitSettingsAction()
    {
        $this->checkOpen();

        $description = $this->contentManager->getContent(array('type' => 'platformDescription'));
        $platformConfig = $this->configHandler->getPlatformConfig();
        $role = $this->roleManager->getRoleByName($platformConfig->getDefaultRole());
        $form = $this->formFactory->create(
            new AdminForm\GeneralType($this->localeManager->getAvailableLocales(), $role, $description),
            $platformConfig
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            try {
                $this->configHandler->setParameters(
                    array(
                        'allow_self_registration' => $form['selfRegistration']->getData(),
                        'locale_language' => $form['localeLanguage']->getData(),
                        'name' => $form['name']->getData(),
                        'support_email' => $form['support_email']->getData(),
                        'default_role' => $form['defaultRole']->getData()->getName(),
                        'redirect_after_login' => $form['redirect_after_login']->getData(),
                        'form_captcha' => $form['formCaptcha']->getData(),
                    )
                );

                $content = $this->request->get('platform_parameters_form');

                if (isset($content['description'])) {
                    if ($description) {
                        $this->contentManager->updateContent($description, $content['description']);
                    } else {
                        $this->contentManager->createContent($content['description'], 'platformDescription');
                    }
                }

                $logo = $this->request->files->get('logo');

                if ($logo) {
                    $this->get('claroline.common.logo_service')->createLogo($logo);
                }
            } catch (UnwritableException $e) {
                $form->addError(
                    new FormError(
                        $this->translator->trans(
                            'unwritable_file_exception',
                            array('%path%' => $e->getPath()),
                            'platform'
                        )
                    )
                );

                return array('form_settings' => $form->createView());
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_parameters_general'));
    }

    /**
     * @EXT\Route("/appearance", name="claro_admin_parameters_appearance")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appearanceFormAction()
    {
        $this->checkOpen();

        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\AppearanceType($this->getThemes()),
            $platformConfig
        );

        return array(
            'form_appearance' => $form->createView(),
            'logos' => $this->get('claroline.common.logo_service')->listLogos()
        );
    }

    /**
     * @EXT\Route("/appearance", name="claro_admin_edit_parameters_appearance")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:appearanceForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAppearanceAction()
    {
        $this->checkOpen();

        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\AppearanceType($this->getThemes()),
            $platformConfig
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            try {
                $this->configHandler->setParameters(
                    array(
                        'nameActive' => $form['name_active']->getData(),
                        'theme' => $form['theme']->getData(),
                        'footer' => $form['footer']->getData(),
                        'logo' => $this->request->get('selectlogo'),
                    )
                );

                $logo = $this->request->files->get('logo');

                if ($logo) {
                    $this->get('claroline.common.logo_service')->createLogo($logo);
                }
            } catch (UnwritableException $e) {
                $form->addError(
                    new FormError(
                        $this->translator->trans(
                            'unwritable_file_exception',
                            array('%path%' => $e->getPath()),
                            'platform'
                        )
                    )
                );

                return array('form_appearance' => $form->createView());
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @EXT\Route("/mail", name="claro_admin_parameters_mail_index")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailIndexAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @EXT\Route("/mail/server", name="claro_admin_parameters_mail_server")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailServerFormAction()
    {
        $this->checkOpen();

        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\MailServerType($platformConfig->getMailerTransport()),
            $platformConfig
        );

        return array('form_mail' => $form->createView());
    }


    /**
     * @EXT\Route("/mail/server", name="claro_admin_edit_parameters_mail_server")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:mailServerForm.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitMailServerAction()
    {
        $this->checkOpen();

        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\MailServerType($platformConfig->getMailerTransport()),
            $platformConfig
        );
        $form->handleRequest($this->request);

        $data = array(
            'transport' => $form['mailer_transport']->getData(),
            'host' => $form['mailer_host']->getData(),
            'username' => $form['mailer_username']->getData(),
            'password' => $form['mailer_password']->getData(),
            'auth_mode' => $form['mailer_auth_mode']->getData(),
            'encryption' => $form['mailer_encryption']->getData(),
            'port' => $form['mailer_port']->getData()
        );

        $settings = new MailingSettings();
        $settings->setTransport($data['transport']);
        $settings->setTransportOptions($data);
        $errors = $settings->validate();

        if (count($errors) > 0) {
            foreach ($errors as $field => $error) {
                $trans = $this->translator->trans($error, array(), 'platform');
                $form->get('mailer_' . $field)->addError(new FormError($trans));
            }

            return array('form_mail' => $form->createView());
        }

        $checker = new MailingChecker($settings);
        $error = $checker->testTransport();

        if ($error != 1) {
            $session = $this->request->getSession();
            $session->getFlashBag()->add('error', $this->translator->trans($error, array(), 'platform'));

            return array('form_mail' => $form->createView());
        }

        $this->configHandler->setParameters(
            array(
                'mailer_transport' => $data['transport'],
                'mailer_host' => $data['host'],
                'mailer_username' => $data['username'],
                'mailer_password' => $data['password'],
                'mailer_auth_mode' => $data['auth_mode'],
                'mailer_encryption' => $data['encryption'],
                'mailer_port' => $data['port']
            )
        );
        $this->cacheManager->refresh();

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @EXT\Route("/mail/registration", name="claro_admin_mail_registration")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registrationMailFormAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(
            new AdminForm\MailInscriptionType(),
            $this->mailManager->getMailInscription()
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/mail/registration", name="claro_admin_edit_mail_registration")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:registrationMailForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo add csfr protection
     */
    public function submitRegistrationMailAction()
    {
        $this->checkOpen();

        $formData = $this->request->get('platform_parameters_form');
        $form = $this->formFactory->create(new AdminForm\MailInscriptionType(), $formData['content']);
        $errors = $this->mailManager->validateInscriptionMail($formData['content']);

        if (count($errors) > 0) {
            foreach ($errors as $language => $errors) {
                foreach ($errors['content'] as $error) {
                    $msg = $this->translator->trans($error, array('%language%' => $language), 'platform');
                    $form->get('content')->addError(new FormError($msg));
                }
            }
        } else {
            $this->contentManager->updateContent($this->mailManager->getMailInscription(), $formData['content']);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/mail/layout", name="claro_admin_mail_layout")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailLayoutFormAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(
            new AdminForm\MailLayoutType(),
            $this->mailManager->getMailLayout()
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/mail/layout", name="claro_admin_edit_mail_layout")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:mailLayoutForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo add csfr protection
     */
    public function submitMailLayoutAction()
    {
        $this->checkOpen();

        $formData = $this->request->get('platform_parameters_form');
        $form = $this->formFactory->create(new AdminForm\MailLayoutType(), $formData['content']);
        $errors = $this->mailManager->validateLayoutMail($formData['content']);

        if (count($errors) > 0) {
            foreach ($errors as $language => $error) {
                $msg = $this->translator->trans($error['content'], array('%language%' => $language), 'platform');
                $form->get('content')->addError(new FormError($msg));
            }
        } else {
            $this->contentManager->updateContent($this->mailManager->getMailLayout(), $formData['content']);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/terms", name="claro_admin_edit_terms_of_service")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function termsOfServiceFormAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(
            new AdminForm\TermsOfServiceType($this->configHandler->getParameter('terms_of_service')),
            $this->termsOfService->getTermsOfService(false)
        );

        return array('form' => $form->createView());
    }

    /**
     * Updates the platform settings and redirects to the settings form.
     *
     * @EXT\Route("/terms", name="claro_admin_edit_terms_of_service_submit")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:termsOfServiceForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitTermsOfServiceAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(
            new AdminForm\TermsOfServiceType($this->configHandler->getParameter('terms_of_service')),
            $this->termsOfService->getTermsOfService(false)
        );

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->configHandler->setParameter('terms_of_service', $form->get('active')->getData());
            $this->termsOfService->setTermsOfService(
                $this->request->get('terms_of_service_form')['termsOfService']
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/indexing", name="claro_admin_parameters_indexing")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexingFormAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new AdminForm\IndexingType(), $this->configHandler->getPlatformConfig());

        if ($this->request->getMethod() === 'POST') {
            $form->handleRequest($this->request);

            if ($form->isValid()) {
                $this->configHandler->setParameter('google_meta_tag', $form['google_meta_tag']->getData());
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/session", name="claro_admin_session")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sessionFormAction()
    {
        $this->checkOpen();

        $config = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\SessionType(), $config);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/session", name="claro_admin_session_submit")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:sessionForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitSessionAction()
    {
        $this->checkOpen();

        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\SessionType($this->configHandler->getParameter('session_storage_type')),
            $platformConfig
        );

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = array(
                'session_storage_type' => $form['session_storage_type']->getData(),
                'session_db_table' => $form['session_db_table']->getData(),
                'session_db_id_col' => $form['session_db_id_col']->getData(),
                'session_db_data_col' => $form['session_db_data_col']->getData(),
                'session_db_time_col' => $form['session_db_time_col']->getData(),
                'session_db_dsn' => $form['session_db_dsn']->getData(),
                'session_db_user' => $form['session_db_user']->getData(),
                'session_db_password' => $form['session_db_password']->getData(),
                'cookie_lifetime' => $form['cookie_lifetime']->getData()
            );

            $errors = $this->dbSessionValidator->validate($data);

            if (count($errors) === 0) {
                $this->configHandler->setParameters($data);
            } else {
                foreach ($errors as $error) {
                    $msg = $this->translator->trans($error, array(), 'platform');
                    $form->addError(new FormError($msg));
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/oauth", name="claro_admin_parameters_oauth_index")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function oauthIndexAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @EXT\Route("/oauth/facebook", name="claro_admin_facebook_form")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function facebookFormAction()
    {
        $this->checkOpen();
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\FacebookType(), $platformConfig);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("delete/logo/{file}", name="claro_admin_delete_logo", options = {"expose"=true})
     *
     * @param $file
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteLogoAction($file)
    {
        try {
            $this->get('claroline.common.logo_service')->deleteLogo($file);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * @EXT\Route("/oauth/facebook", name="claro_admin_facebook_form_submit")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Parameters:facebookForm.html.twig")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitFacebookFormAction()
    {
        $this->checkOpen();
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\FacebookType(), $platformConfig);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = array(
                'facebook_client_id' => $form['facebook_client_id']->getData(),
                'facebook_client_secret' => $form['facebook_client_secret']->getData(),
                'facebook_client_active' => $form['facebook_client_active']->getData()
            );

            $errors = $this->hwiManager->validateFacebook(
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

    /**
     *  Returns the list of available themes.
     *
     *  @return array
     */
    private function getThemes()
    {
        $tmp = array();

        foreach ($this->get('claroline.common.theme_service')->getThemes() as $theme) {
            $tmp[str_replace(' ', '-', strtolower($theme->getName()))] = $theme->getName();
        }

        return $tmp;
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->paramAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
