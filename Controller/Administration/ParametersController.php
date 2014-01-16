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

use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Icap\BlogBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @DI\InjectParams({
     *     "configHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "localeManager"  = @DI\Inject("claroline.common.locale_manager"),
     *     "termsOfService" = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "request"        = @DI\Inject("request")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        RoleManager $roleManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        TermsOfServiceManager $termsOfService,
        Request $request
    )
    {
        $this->configHandler = $configHandler;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->termsOfService = $termsOfService;
        $this->localeManager = $localeManager;
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
     *     name="claro_admin_parameters_general",
     *     options={"expose"=true}
     * )
     * @Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
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
     * @Route(
     *     "/general/submit",
     *     name="claro_admin_edit_parameters_general"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
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
                        'locale_language' => $form['localeLanguage']->getData(),
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
     * @Route(
     *     "/appearance",
     *     name="claro_admin_parameters_appearance",
     *     options={"expose"=true}
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
     * @Route(
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
     * @Route(
     *     "/mail",
     *     name="claro_admin_parameters_mail",
     *     options={"expose"=true}
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:mail.html.twig")
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
     * @Route(
     *     "/mail/submit",
     *     name="claro_admin_edit_parameters_mail"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration\platform:settings.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitMailAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_PLATFORM_PARAMETERS);
        $form->handleRequest($this->request);

        /*if ($form->isValid()) {
            throw new \Exception('lolilol');
        }*/

        return $this->redirect($this->generateUrl('claro_admin_index'));
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
        return array(
            'langs' => $this->localeManager->getAvailableLocales(),
            'isActive' => $this->configHandler->getParameter('terms_of_service'),
            'termsOfService' => $this->termsOfService->getAvailableTermsOfService()
        );
    }

    /**
     * Updates the platform settings and redirects to the settings form.
     *
     * @Route("/terms_of_service/submit", name="claro_admin_edit_terms_of_service_submit")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitTermsOfServiceAction()
    {
        if ($this->request->get('isActive')) {
            $this->configHandler->setParameter('terms_of_service', true);
        } else {
            $this->configHandler->setParameter('terms_of_service', false);
        }

        /*$termOfService = $this->request->get('termOfService');
        throw new \Exception(var_dump($termOfService));*/

        return $this->redirect($this->generateUrl('claro_admin_index'));
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
