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

use Claroline\CoreBundle\Form\Administration\InternationalizationType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @EXT\Route("/internationalization")
 *
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class InternationalizationController extends Controller
{
    /**
     * Displays i18n form.
     *
     * @EXT\Route("", name="claro_admin_i18n_form")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return array
     */
    public function formAction()
    {
        $localeManager = $this->container->get('claroline.manager.locale_manager');

        $availableLocales = $localeManager->getImplementedLocales();
        $activatedLocales = $localeManager->retrieveAvailableLocales();

        $form = $this->createForm(new InternationalizationType($activatedLocales, $availableLocales));

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Submits i18n form.
     *
     * @EXT\Route("", name="claro_admin_i18n_submit")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:Internationalization\form.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function submitAction()
    {
        $localeManager = $this->container->get('claroline.manager.locale_manager');

        $availableLocales = $localeManager->getImplementedLocales();
        $activatedLocales = $localeManager->retrieveAvailableLocales();

        $form = $this->createForm(new InternationalizationType($activatedLocales, $availableLocales));

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $data = $form->get('locales')->getData();
            $this->container->get('claroline.config.platform_config_handler')->setParameter('locales', $data);

            return new RedirectResponse($this->get('router')->generate('claro_admin_parameters_index'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
