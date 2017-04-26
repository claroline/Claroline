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

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Service("claroline.manager.locale_manager")
 */
class LocaleManager
{
    private $defaultLocale;
    private $finder;
    private $locales;
    private $userManager;
    private $tokenStorage;
    private $configHandler;

    /**
     * @InjectParams({
     *     "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "userManager"    = @Inject("claroline.manager.user_manager"),
     *     "tokenStorage"   = @Inject("security.token_storage")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->configHandler = $configHandler;
        $this->userManager = $userManager;
        $this->defaultLocale = $configHandler->getParameter('locale_language');
        $this->finder = new Finder();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get a list of available languages in the platform.
     *
     * @param $path The path of translations files
     *
     * @return array
     */
    public function retrieveAvailableLocales($path = '/../Resources/translations/')
    {
        $locales = [];
        $data = $this->configHandler->getParameter('locales');

        foreach ($data as $locale) {
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    /**
     * Get a list of available languages in the platform.
     *
     * @param $path The path of translations files
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

    /**
     * Get a list of available languages in the platform.
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        if (!$this->locales) {
            $this->locales = $this->retrieveAvailableLocales();
        }

        return $this->locales;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string The locale string as en, fr, es, etc
     */
    public function getUserLocale(Request $request)
    {
        $locales = $this->getAvailableLocales();
        $preferred = explode('_', $request->getPreferredLanguage());

        if ($request->attributes->get('_locale')) {
            $locale = $request->attributes->get('_locale');
        } elseif (($user = $this->getCurrentUser()) && $user->getLocale()) {
            $locale = $user->getLocale();
        } elseif ($request->getSession() && ($sessionLocale = $request->getSession()->get('_locale'))) {
            $locale = $sessionLocale;
        } elseif (count($preferred) > 0 && isset($locales[$preferred[0]])) {
            $locale = $preferred[0];
        } else {
            $locale = $this->defaultLocale;
        }

        if ($session = $request->getSession()) {
            $session->set('_locale', $locale);
        }

        return $locale;
    }

    public function getLocaleListForSelect()
    {
        $locales = $this->retrieveAvailableLocales();
        $labels = $this->getLocalesLabels();
        $keys = array_keys($labels);
        $data = [];

        foreach ($locales as $locale) {
            if (in_array($locale, $keys)) {
                $data[] = ['value' => $locale, 'label' => $labels[$locale]];
            }
        }

        return $data;
    }

    public function getLocalesLabels()
    {
        return [
            'fr' => 'Français',
            'en' => 'English',
            'nl' => 'Nederlands',
            'es' => 'Español',
            'it' => 'Italiano',
            'de' => 'Deutsch',
        ];
    }

    /**
     * Get Current User.
     *
     * @return mixed Claroline\CoreBundle\Entity\User or null
     */
    private function getCurrentUser()
    {
        if (is_object($token = $this->tokenStorage->getToken()) && is_object($user = $token->getUser())) {
            return $user;
        }
    }
}
