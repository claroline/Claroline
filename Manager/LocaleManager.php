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
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @Service("claroline.common.locale_manager")
 */
class LocaleManager
{
    private $context;
    private $defaultLocale;
    private $finder;
    private $locales;
    private $manager;
    private $session;

    /**
     * @InjectParams({
     *     "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "context"        = @Inject("security.context"),
     *     "manager"        = @Inject("claroline.persistence.object_manager"),
     *     "session"        = @Inject("session")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler, SecurityContext $context, ObjectManager $manager, Session $session
    )
    {
        $this->context = $context;
        $this->defaultLocale = $configHandler->getParameter('locale_language');
        $this->finder = new Finder();
        $this->manager = $manager;
        $this->locales = $this->retriveAvailableLocales();
        $this->session = $session;
    }

    private function retriveAvailableLocales($path = '/../Resources/translations/')
    {
        $locales = array();
        $finder = $this->finder->files()->in(__DIR__.$path)->name('/platform\.[^.]*\.yml/');

        foreach ($finder as $file) {
            $locale = str_replace(array('platform.', '.yml'), '', $file->getRelativePathname());
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    /**
     * Get a list of available languages in the platform.
     */
    public function getAvailableLocales()
    {
        return $this->locales;
    }

    /**
     * Set the user locale.
     */
    public function setUserLocale($locale = 'en')
    {
        $user = $this->context->getToken()->getUser();

        if (isset($this->locales[$locale]) and is_object($user)) {
            $user->setLocale($locale);
            $this->manager->persist($user);
            $this->manager->flush();
        }
    }

    public function getUserLocale()
    {
        if (is_object($token = $this->context->getToken()) and
            is_object($user = $token->getUser()) and ($locale = $user->getLocale()) !== '') {
            return $locale;
        }
    }

    public function preferredLocale($request)
    {
        $preferred = explode('_', $request->getPreferredLanguage());

        if (isset($preferred[0]) and isset($this->locales[$preferred[0]])) {
            $this->defaultLocale = $preferred[0];
        }
    }

    public function setRequestLocale($request)
    {
        if ($locale = $this->getUserLocale()) {
            $request->getSession()->set('_locale', $locale);
        }

        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        }

        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
    }
}
