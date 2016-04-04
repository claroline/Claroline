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

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\Administration\InternationalizationType;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
 */
class InternationalizationController extends Controller
{
    /**
     * @EXT\Route("/internationalization/form", name="claro_admin_i18n_form")
     * @EXT\Template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $localeManager = $this->container->get('claroline.common.locale_manager');
        $availableLocales = $localeManager->getImplementedLocales();
        $activatedLocales = $localeManager->retrieveAvailableLocales();
        $form = $this->createForm(new InternationalizationType($activatedLocales, $availableLocales));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/internationalization/submit", name="claro_admin_i18n_submit")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAction()
    {
        $localeManager = $this->container->get('claroline.common.locale_manager');
        $availableLocales = $localeManager->getImplementedLocales();
        $activatedLocales = $localeManager->retrieveAvailableLocales();
        $form = $this->createForm(new InternationalizationType($activatedLocales, $availableLocales));
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $data = $form->get('locales')->getData();
            $this->container->get('claroline.config.platform_config_handler')->setParameter('locales', $data);

            return new RedirectResponse($this->get('router')->generate('claro_admin_parameters_index'));
        }

       return $this->render(
           'ClarolineCoreBundle:Administration:Internationalization\form.html.twig',
           array('form' => $form->createView(), 'product' => $product)
       );
    }
}
