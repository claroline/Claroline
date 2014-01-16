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
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Service("claroline.common.terms_of_service_manager")
 */
class TermsOfServiceManager
{
    private $isActive;
    private $finder;
    private $termsOfService;
    private $userManager;
    private $request;

    /**
     * @InjectParams({
     *     "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "userManager"    = @Inject("claroline.manager.user_manager"),
     *     "requestStack"   = @Inject("request_stack")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        UserManager $userManager,
        RequestStack $requestStack
    )
    {
        $this->userManager = $userManager;
        $this->isActive = $configHandler->getParameter('terms_of_service');
        $this->finder = new Finder();
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Check if the terms of service functionality is active
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Get a list of availables terms of service in the platform.
     *
     * @param $path The path of translations files
     *
     * @return Array
     */
    private function retriveAvailableTermsOfService($path = '/../../../../../../web/uploads/tos/')
    {
        $termsOfService = array();
        $finder = $this->finder->files()->in(__DIR__.$path)->name('/termsOfService\.[^.]*\.txt/');

        foreach ($finder as $file) {
            $locale = str_replace(array('termsOfService.', '.txt'), '', $file->getRelativePathname());
            $termsOfService[$locale] = file_get_contents($file->getRealpath());
        }

        return $termsOfService;
    }

    /**
     * Get a list of availables terms of service in the platform.
     *
     * @return Array
     */
    public function getAvailableTermsOfService()
    {
        if (!$this->termsOfService) {
            $this->termsOfService = $this->retriveAvailableTermsOfService();
        }

        return $this->termsOfService;
    }

    public function getTermsOfService()
    {
        if (!$this->request) {
             throw new NoHttpRequestException();
        }

        $termsOfService = $this->getAvailableTermsOfService();

        if (!empty($termsOfService)) {
            $preferred = explode('_', $this->request->getPreferredLanguage());
            $currentLocale = $this->request->attributes->get('_locale');
            $sessionLocale = $this->request->getSession()->get('_locale');

            if ($currentLocale and isset($termsOfService[$currentLocale])) {
                return $termsOfService[$currentLocale];
            }

            if ($sessionLocale and isset($termsOfService[$sessionLocale])) {
                return $termsOfService[$sessionLocale];
            }

            if (isset($preferred[0]) and isset($termsOfService[$preferred[0]])) {
                return $termsOfService[$preferred[0]];
            }

            return array_values($termsOfService)[0];
        }

        return null;
    }
}
