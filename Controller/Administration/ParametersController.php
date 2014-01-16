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

use Icap\BlogBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Symfony\Component\Form\FormError;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Installation\Settings\MailingSettings;
use Claroline\CoreBundle\Library\Installation\Settings\MailingChecker;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
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

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory"   = @DI\Inject("claroline.form.factory"),
     *     "localeManager" = @DI\Inject("claroline.common.locale_manager"),
     *     "request"       = @DI\Inject("request"),
     *     "translator"    = @DI\Inject("translator")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        RoleManager $roleManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        Request $request,
        Translator $translator
    )
    {
        $this->configHandler = $configHandler;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:index.html.twig")
     * @EXT\Route(
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
     * @EXT\Route(
     *     "/general",
     *     name="claro_admin_parameters_general",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $role = $this->roleManager->getRoleByName($platformConfig->getDefaultRole());
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_PARAMETERS,
            array($this->localeManager->getAvailableLocales(), $role),
            $platformConfig
        );

        return array(
            'form_settings' => $form->createView(),
            'logos' => $this->get('claroline.common.logo_service')->listLogos()
        );
    }

    /**
     * @EXT\Route(
     *     "/general/submit",
     *     name="claro_admin_edit_parameters_general"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitSettingsAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $role = $this->roleManager->getRoleByName($platformConfig->getDefaultRole());
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_PARAMETERS,
            array($this->localeManager->getAvailableLocales(), $role),
            $platformConfig
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            try {
                $this->configHandler->setParameters(
                    array(
                        'allow_self_registration' => $form['selfRegistration']->getData(),
                        'locale_language' => $form['localLanguage']->getData(),
                        'name' => $form['name']->getData(),
                        'support_email' => $form['support_email']->getData(),
                        'default_role' => $form['defaultRole']->getData()->getName(),
                        'cookie_lifetime' => $form['cookie_lifetime']->getData()
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

                return array('form_settings' => $form->createView());
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @EXT\Route(
     *     "/appearance",
     *     name="claro_admin_parameters_appearance",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:appearance.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appearanceFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_APPEARANCE,
            array($this->getThemes()),
            $platformConfig
        );

        return array(
            'form_appearance' => $form->createView(),
            'logos' => $this->get('claroline.common.logo_service')->listLogos()
        );
    }

    /**
     * @EXT\Route(
     *     "/appearance/submit",
     *     name="claro_admin_edit_parameters_appearance",
     *     options={"expose"=true}
     * )
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAppearanceAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_APPEARANCE,
            array($this->getThemes()),
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
     * @EXT\Route(
     *     "/mail",
     *     name="claro_admin_parameters_mail_server",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform\mail:server.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_MAIL_SETTINGS,
            array($platformConfig->getMailerTransport()),
            $platformConfig
        );

        return array('form_mail' => $form->createView());
    }


    /**
     * @EXT\Route(
     *     "/mail/submit",
     *     name="claro_admin_edit_parameters_mail_server"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform\mail:server.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitMailAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_MAIL_SETTINGS,
            array($platformConfig->getMailerTransport()),
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

        return $this->redirect($this->generateUrl('claro_admin_index'));
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Administration\platform\mail:index.html.twig")
     * @EXT\Route(
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
     *  Get the list of themes availables.
     *  @TODO use directory iterator
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
