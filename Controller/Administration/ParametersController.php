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
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Library\Installation\Settings\MailingSettings;
use Claroline\CoreBundle\Library\Installation\Settings\MailingChecker;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Icap\BlogBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Form\Administration as AdminForm;
use Claroline\CoreBundle\Manager\CacheManager;
use Claroline\CoreBundle\Library\Installation\Refresher;
use Claroline\CoreBundle\Manager\HwiManager;

/**
 * @DI\Tag("security.secure_service")
 * @PreAuthorize("hasRole('ADMIN')")
 *
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
     *     "hwiManager"         = @DI\Inject("claroline.manager.hwi_manager")
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
        HwiManager $hwiManager
    )
    {
        $this->configHandler = $configHandler;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->termsOfService = $termsOfService;
        $this->localeManager = $localeManager;
        $this->translator = $translator;
        $this->mailManager = $mailManager;
        $this->contentManager = $contentManager;
        $this->cacheManager = $cacheManager;
        $this->dbSessionValidator = $sessionValidator;
        $this->refresher = $refresher;
        $this->hwiManager = $hwiManager;
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform:index.html.twig")
     * @Route(
     *     "/index",
     *     name="claro_admin_parameters_index"
     * )
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "/general",
     *     name="claro_admin_parameters_general"
     * )
     * @Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsFormAction()
    {
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
     * @Route(
     *     "/general/submit",
     *     name="claro_admin_edit_parameters_general"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     * @Method("POST")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitSettingsAction()
    {
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
                        'redirect_after_login' => $form['redirect_after_login']->getData()
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

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @Route(
     *     "/appearance",
     *     name="claro_admin_parameters_appearance"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:appearance.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appearanceFormAction()
    {
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
     * @Route(
     *     "/appearance/submit",
     *     name="claro_admin_edit_parameters_appearance"
     * )
     *
     * @Method("POST")
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAppearanceAction()
    {
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
     * @Route(
     *     "/mail/server",
     *     name="claro_admin_parameters_mail_server"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:server.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailServerFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            new AdminForm\MailServerType($platformConfig->getMailerTransport()),
            $platformConfig
        );

        return array('form_mail' => $form->createView());
    }


    /**
     * @Route(
     *     "/mail/server/submit",
     *     name="claro_admin_edit_parameters_mail_server"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:server.html.twig")
     * @Method("POST")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitMailServerAction()
    {
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
     * @Route(
     *     "/mail/registration",
     *     name="claro_admin_mail_registration"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:registration.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function inscriptionMailFormAction()
    {
        $form = $this->formFactory->create(
            new AdminForm\MailInscriptionType(),
            $this->mailManager->getMailInscription()
        );

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/mail/submit/registration",
     *     name="claro_admin_edit_mail_registration"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:registration.html.twig")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo add csfr protection
     */
    public function submitInscriptionMailAction()
    {
        $form = $this->request->get('platform_parameters_form');
        $errors = $this->mailManager->validateInscriptionMail($form['content']);

        if (count($errors) === 0) {
            if (isset($form['content'])) {
                $this->contentManager->updateContent($this->mailManager->getMailInscription(), $form['content']);
            }

            return $this->redirect($this->generateUrl('claro_admin_index'));
        }

        $formWithErrors = $this->formFactory->create(
            new AdminForm\MailInscriptionType(),
            $form['content']
        );

        foreach ($errors as $language => $errors) {
            foreach ($errors['content'] as $error) {
                $trans = $this->translator->trans($error, array('%language%' => $language), 'platform');
                $formWithErrors->get('content')->addError(new FormError($trans));
            }
        }

        return array('form' => $formWithErrors->createView());
    }

    /**
     * @Route(
     *     "/mail/layout",
     *     name="claro_admin_mail_layout"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:layout.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailLayoutFormAction()
    {
        $form = $this->formFactory->create(
            new AdminForm\MailLayoutType(),
            $this->mailManager->getMailLayout()
        );

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/mail/layout/submit",
     *     name="claro_admin_edit_mail_layout"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform\mail:layout.html.twig")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo add csfr protection
     */
    public function submitMailLayoutAction()
    {
        $form = $this->request->get('platform_parameters_form');
        $errors = $this->mailManager->validateLayoutMail($form['content']);

        if (count($errors) === 0) {
            if (isset($form['content'])) {
                $this->contentManager->updateContent($this->mailManager->getMailLayout(), $form['content']);
            }

            return $this->redirect($this->generateUrl('claro_admin_index'));
        }

        $formWithErrors = $this->formFactory->create(
            new AdminForm\MailLayoutType(),
            $form['content']
        );

        foreach ($errors as $language => $error) {
            $trans = $this->translator->trans($error['content'], array('%language%' => $language), 'platform');
            $formWithErrors->get('content')->addError(new FormError($trans));
        }

        return array('form' => $formWithErrors->createView());
    }

    /**
     * @Route(
     *     "/terms_of_service",
     *     name="claro_admin_edit_terms_of_service"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:termsOfService.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function termsOfServiceAction()
    {
        $form = $this->formFactory->create(
            new AdminForm\TermsOfServiceType($this->configHandler->getParameter('terms_of_service')),
            $this->termsOfService->getTermsOfService(false)
        );

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Updates the platform settings and redirects to the settings form.
     *
     * @Route("/terms_of_service/submit", name="claro_admin_edit_terms_of_service_submit")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitTermsOfServiceAction()
    {

        if ($termsOfService = $this->request->get('terms_of_service_form')) {

            if (isset($termsOfService['active'])) {
                $this->configHandler->setParameter('terms_of_service', true);
            } else {
                $this->configHandler->setParameter('terms_of_service', false);
            }

            if (isset($termsOfService['termsOfService'])) {
                $this->termsOfService->setTermsOfService($termsOfService['termsOfService']);
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform\mail:index.html.twig")
     * @Route(
     *     "/mail/index",
     *     name="claro_admin_parameters_mail_index"
     * )
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailIndexAction()
    {
        return array();
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform:indexing.html.twig")
     * @Route(
     *     "/indexing",
     *     name="claro_admin_parameters_indexing"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexingAction()
    {
        $form = $this->formFactory->create(new AdminForm\IndexingType(), $this->configHandler->getPlatformConfig());

        if ($this->request->getMethod() === 'POST') {
            $form->handleRequest($this->request);

            if ($form->isValid()) {
                $this->configHandler->setParameter('google_meta_tag', $form['google_meta_tag']->getData());
                return $this->redirect($this->generateUrl('claro_admin_index'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/session",
     *     name="claro_admin_session"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:session.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sessionAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();

        $form = $this->formFactory->create(
            new AdminForm\SessionType(),
            $platformConfig
        );

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route(
     *     "/session/submit",
     *     name="claro_admin_session_submit"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:session.html.twig")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitSessionAction()
    {
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

                return $this->redirect($this->generateUrl('claro_admin_index'));
            }

            foreach ($errors as  $error) {
                $trans = $this->translator->trans($error, array(), 'platform');
                $form->addError(new FormError($trans));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform\oauth:index.html.twig")
     * @Route(
     *     "/oauth/index",
     *     name="claro_admin_parameters_oauth_index"
     * )
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function oauthIndexAction()
    {
        return array();
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform\oauth:facebook.html.twig")
     * @Route(
     *     "/oauth/facebook/form",
     *     name="claro_admin_facebook_form"
     * )
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function facebookSettingsFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\FacebookType(), $platformConfig);

        return array('form' => $form->createView());
    }

    /**
     * @Template("ClarolineCoreBundle:Administration\platform\oauth:facebook.html.twig")
     * @Route(
     *     "/oauth/facebook/submit",
     *     name="claro_admin_facebook_form_submit"
     * )
     * @Method("POST")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function facebookSettingsSubmitAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\FacebookType(), $platformConfig);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = array(
                'facebook_client_id' => $form['facebook_client_id']->getData(),
                'facebook_client_secret' => $form['facebook_client_secret']->getData()
            );

            $errors = $this->hwiManager->validateFacebook($data['facebook_client_id'], $data['facebook_client_secret']);

            if (count($errors) === 0) {
                $this->configHandler->setParameters($data);
                $this->cacheManager->refresh();

                return $this->redirect($this->generateUrl('claro_admin_index'));
            }

            foreach ($errors as  $error) {
                $trans = $this->translator->trans($error, array(), 'platform');
                $form->addError(new FormError($trans));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     *  Get the list of themes availables.
     *
     *  @return array with a list of the themes availables.
     */
    private function getThemes()
    {
        $tmp = array();

        foreach ($this->get('claroline.common.theme_service')->getThemes() as $theme) {
            $tmp[str_replace(' ', '-', strtolower($theme->getName()))] = $theme->getName();
        }

        return $tmp;
    }
}
