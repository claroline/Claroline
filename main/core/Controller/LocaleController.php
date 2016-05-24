<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @TODO doc
 */
class LocaleController
{
    private $localeManager;
    private $tokenStorage;

    /**
     * @InjectParams({
     *     "localeManager"   = @Inject("claroline.manager.locale_manager"),
     *     "tokenStorage"    = @Inject("security.token_storage")
     * })
     */
    public function __construct(LocaleManager $localeManager, TokenStorageInterface $tokenStorage)
    {
        $this->localeManager = $localeManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Select a language.
     *
     * @Route("/locale/select", name="claroline_locale_select", options = {"expose" = true})
     *
     * @Template("ClarolineCoreBundle:Locale:select.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectLangAction()
    {
        return array('locales' => $this->localeManager->getAvailableLocales());
    }

    /**
     * Change locale.
     *
     * @Route("/locale/change/{locale}", name="claroline_locale_change", options = {"expose" = true})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $locale
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeLocale(Request $request, $locale)
    {
        if (($token = $this->tokenStorage->getToken()) && $token->getUser() !== 'anon.') {
            $this->localeManager->setUserLocale($locale);
        }
        $request->getSession()->set('_locale', $locale);

        return new Response('Locale changed to '.$locale, 200);
    }
}
