<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ContentManager;

class TermsOfServiceManager
{
    private $config;
    private $contentManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        ContentManager $contentManager
    ) {
        $this->config = $config;
        $this->contentManager = $contentManager;
    }

    /**
     * Checks if terms of service functionality is active.
     *
     * @return bool
     */
    public function isActive()
    {
        // TODO : check it's not empty too

        return $this->config->getParameter('tos.enabled') ?? false;
    }

    public function getTermsOfService($translations = true)
    {
        if ($translations) {
            return $this->contentManager->getTranslatedContent(['type' => 'termsOfService']);
        }

        return $this->contentManager->getContent(['type' => 'termsOfService']);
    }

    public function getLocalizedTermsOfService(string $locale = null)
    {
        $terms = null;

        $allTerms = $this->getTermsOfService();
        if (!empty($allTerms)) {
            if ($locale && !empty($allTerms[$locale])) {
                $terms = $allTerms[$locale];
            } elseif (!empty($allTerms[$this->config->getParameter('locales.default')])) {
                $terms = $allTerms[$this->config->getParameter('locales.default')];
            } else {
                $terms = array_shift($allTerms);
            }
        }

        if ($terms) {
            return $terms['content'];
        }

        return null;
    }
}
