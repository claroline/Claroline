<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LocaleManager
{
    private $defaultLocale;
    private $finder;
    private $userManager;
    private $tokenStorage;
    private $configHandler;

    public function __construct(
        PlatformConfigurationHandler $configHandler,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->configHandler = $configHandler;
        $this->userManager = $userManager;
        $this->defaultLocale = $configHandler->getParameter('locales.default');
        $this->finder = new Finder();
        $this->tokenStorage = $tokenStorage;
    }

    public function getLocales()
    {
        $available = $this->getAvailableLocales();
        $implemented = array_keys($this->getImplementedLocales());

        return array_map(function ($locale) use ($available) {
            return [
                'name' => $locale,
                'enabled' => array_key_exists($locale, $available),
            ];
        }, $implemented);
    }

    /**
     * Get a list of available languages in the platform.
     *
     * @param string $path The path of translations files
     *
     * @return array
     */
    public function getImplementedLocales($path = '/../Resources/translations/')
    {
        $locales = [];
        $finder = $this->finder->files()->in(__DIR__.$path)->name('/platform\.[^.]*\.json/');

        foreach ($finder as $file) {
            $locale = str_replace(['platform.', '.json'], '', $file->getRelativePathname());
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    public function getDefault()
    {
        return $this->configHandler->getParameter('locales.default');
    }

    /**
     * Get a list of available languages in the platform.
     */
    public function getAvailableLocales(): array
    {
        $locales = [];

        $data = $this->configHandler->getParameter('locales.available');
        foreach ($data as $locale) {
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    /**
     * Set locale setting for current user if this locale is present in the platform.
     *
     * @param string $locale The locale string as en, fr, es, etc
     */
    public function setUserLocale($locale)
    {
        $this->userManager->setLocale($this->getCurrentUser(), $locale);
    }

    /**
     * This method returns the user locale and store it in session, if there is no user this method return default
     * language or the browser language if it is present in translations.
     *
     * @return string The locale string as en, fr, es, etc
     */
    public function getUserLocale(Request $request)
    {
        $locales = $this->getAvailableLocales();
        $preferred = explode('_', $request->getPreferredLanguage());

        if ($request->query->get('_locale')) {
            $locale = $request->query->get('_locale');
        } elseif ($request->attributes->get('_locale')) {
            $locale = $request->attributes->get('_locale');
        } elseif ($this->getCurrentUser() && $this->getCurrentUser()->getLocale()) {
            $locale = $this->getCurrentUser()->getLocale();
        } elseif ($request->getSession() && $request->getSession()->get('_locale')) {
            $locale = $request->getSession()->get('_locale');
        } elseif (count($preferred) > 0 && isset($locales[$preferred[0]])) {
            $locale = $preferred[0];
        } else {
            $locale = $this->defaultLocale;
        }

        $session = $request->getSession();
        if ($session) {
            $session->set('_locale', $locale);
        }

        return $locale;
    }

    /**
     * Get Current User.
     *
     * @return User|null
     */
    private function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();
        if (is_object($token)) { // not sure this check is still required
            $user = $token->getUser();
            if ($user instanceof User) {
                return $user;
            }
        }

        return null;
    }

    public function getLocale(User $user)
    {
        return $user->getLocale() ? $user->getLocale() : $this->defaultLocale;
    }
}
