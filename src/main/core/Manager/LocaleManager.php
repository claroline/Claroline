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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LocaleManager
{
    public function __construct(
        private readonly PlatformConfigurationHandler $configHandler,
        private readonly UserManager $userManager,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function getLocales(): array
    {
        $enabled = $this->getEnabledLocales();
        $available = $this->getAvailableLocales();

        return array_map(function ($locale) use ($enabled) {
            return [
                'name' => $locale,
                'enabled' => in_array($locale, $enabled),
            ];
        }, $available);
    }

    public function getDefault(): ?string
    {
        return $this->configHandler->getParameter('locales.default');
    }

    /**
     * Get the list of all available languages in the platform.
     */
    public function getAvailableLocales(): array
    {
        return ['en', 'fr'];
    }

    /**
     * Get the list of enabled languages in the platform.
     */
    public function getEnabledLocales(): array
    {
        return $this->configHandler->getParameter('locales.available') ?? [];
    }

    /**
     * Set locale setting for current user if this locale is present in the platform.
     *
     * @param string $locale The locale string as en, fr, es, etc
     */
    public function setUserLocale(string $locale): void
    {
        $this->userManager->setLocale($this->getCurrentUser(), $locale);
    }

    /**
     * This method returns the user locale and store it in session, if there is no user this method return default
     * language or the browser language if it is present in translations.
     *
     * @return string The locale string as en, fr, es, etc
     */
    public function getUserLocale(Request $request): string
    {
        $locales = $this->getEnabledLocales();
        $preferred = explode('_', $request->getPreferredLanguage());

        if ($request->query->get('_locale')) {
            $locale = $request->query->get('_locale');
        } elseif ($request->attributes->get('_locale')) {
            $locale = $request->attributes->get('_locale');
        } elseif ($this->getCurrentUser() && $this->getCurrentUser()->getLocale()) {
            $locale = $this->getCurrentUser()->getLocale();
        } elseif ($request->getSession() && $request->getSession()->get('_locale')) {
            $locale = $request->getSession()->get('_locale');
        } elseif (count($preferred) > 0 && in_array($preferred[0], $locales)) {
            $locale = $preferred[0];
        } else {
            $locale = $this->getDefault();
        }

        return $locale;
    }

    private function getCurrentUser(): ?User
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

    public function getLocale(User $user): string
    {
        return $user->getLocale() ?: $this->getDefault();
    }

    public function getLocaleDate(\DateTimeInterface $date): \DateTimeInterface
    {
        $timezone = $this->configHandler->getParameter('intl.timezone');

        $dateTimezone = new \DateTimeZone($timezone ?: 'UTC');

        $localeDate = clone $date;
        $localeDate->setTimezone($dateTimezone);

        return $localeDate;
    }

    public function getLocaleDateFormat(\DateTimeInterface $date): string
    {
        $localeDate = $this->getLocaleDate($date);
        $dateFormat = $this->configHandler->getParameter('intl.dateFormat') ?: 'Y-m-d';

        return $localeDate->format($dateFormat);
    }

    public function getLocaleDateTimeFormat(\DateTimeInterface $date): string
    {
        $localeDate = $this->getLocaleDate($date);
        $dateFormat = $this->configHandler->getParameter('intl.dateFormat') ?: 'Y-m-d';
        $timeFormat = $this->configHandler->getParameter('intl.timeFormat') ?: 'H:i';

        return $localeDate->format($dateFormat.' '.$timeFormat);
    }
}
