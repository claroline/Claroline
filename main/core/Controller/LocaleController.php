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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/locale", options={"expose" = true})
 */
class LocaleController
{
    /** @var LocaleManager */
    private $localeManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * LocaleController constructor.
     *
     * @DI\InjectParams({
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage")
     * })
     *
     * @param LocaleManager         $localeManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        LocaleManager $localeManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->localeManager = $localeManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Change locale.
     *
     * @EXT\Route("/change/{locale}", name="claroline_locale_change")
     *
     * @param Request $request
     * @param string  $locale
     *
     * @return RedirectResponse
     */
    public function changeAction(Request $request, $locale)
    {
        if (($token = $this->tokenStorage->getToken()) && 'anon.' !== $token->getUser()) {
            $this->localeManager->setUserLocale($locale);
        }

        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        return new RedirectResponse(
            $request->headers->get('referer')
        );
    }
}
