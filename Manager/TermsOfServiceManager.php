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
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Entity\Content;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.common.terms_of_service_manager")
 */
class TermsOfServiceManager
{
    private $isActive;
    private $contentManager;

    /**
     * @DI\InjectParams({
     *     "configHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "contentManager" = @DI\Inject("claroline.manager.content_manager")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        ContentManager $contentManager
    )
    {
        $this->contentManager = $contentManager;
        $this->isActive = $configHandler->getParameter('terms_of_service');
    }

    /**
     * Checks if terms of service functionality is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    public function getTermsOfService($translations = true)
    {
        if ($translations) {
            return $this->contentManager->getTranslatedContent(array('type' => 'termsOfService'));
        }

        return $this->contentManager->getContent(array('type' => 'termsOfService'));
    }

    /**
     * Checks if terms are available in at least one language in a set of translated terms.
     *
     * @param array $translatedTerms    An associative array in which each key is a language code
     *                                  and each value an associative array with a "content" key.
     *
     * @return bool
     */
    public function areTermsEmpty(array $translatedTerms)
    {
        foreach ($translatedTerms as $term) {
            if (!empty($term['content'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Persists (creates/updates) terms of service in the database.
     *
     * @param array $translatedTerms    An associative array in which each key is a language code
     *                                  and each value an associative array with a "content" key.
     */
    public function setTermsOfService(array $translatedTerms)
    {
        $terms = $this->contentManager->getContent(array('type' => 'termsOfService'));

        if ($terms instanceof Content) {
            $this->contentManager->updateContent($terms, $translatedTerms);
        } else {
            $this->contentManager->createContent($translatedTerms, 'termsOfService');
        }
    }

    public function deleteTermsOfService($locale)
    {
        $termsOfService = $this->contentManager->getContent(array('type' => 'termsOfService'));

        if ($termsOfService instanceof Content) {
            $this->contentManager->deleteTranslation($locale, $termsOfService->getId());
        }
    }
}
