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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory"   = @DI\Inject("claroline.form.factory"),
     *     "localeManager" = @DI\Inject("claroline.common.locale_manager"),
     *     "request"       = @DI\Inject("request")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        RoleManager $roleManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        Request $request
    )
    {
        $this->configHandler = $configHandler;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->localeManager = $localeManager;
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
     *     name="claro_admin_parameters_mail",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:mail.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_PLATFORM_MAIL_SETTINGS);

        return array(
            'form_mail' => $form->createView(),
            'logos' => $this->get('claroline.common.logo_service')->listLogos()
        );
    }


    /**
     * @EXT\Route(
     *     "/mail/submit",
     *     name="claro_admin_edit_parameters_mail"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitMailAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_PLATFORM_PARAMETERS);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
//            throw new \Exception('lolilol');
        }

        return $this->redirect($this->generateUrl('claro_admin_index'));
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