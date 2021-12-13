<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Manages platform locales.
 *
 * @Route("/locale")
 */
class LocaleController
{
    /** @var LocaleManager */
    private $localeManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        LocaleManager $localeManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->localeManager = $localeManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * List platform locales.
     *
     * @Route("/", name="apiv2_locale_list", methods={"GET"})
     */
    public function listAction(): JsonResponse
    {
        return new JsonResponse(
            $this->localeManager->getLocales()
        );
    }

    /**
     * Change locale.
     *
     * @Route("/{locale}", name="claroline_locale_change")
     */
    public function changeAction(Request $request, string $locale): RedirectResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $this->localeManager->setUserLocale($locale);
        }

        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        return new RedirectResponse(
            $request->headers->get('referer')
        );
    }
}
