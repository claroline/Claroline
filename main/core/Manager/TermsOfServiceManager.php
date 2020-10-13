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

use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;

class TermsOfServiceManager
{
    private $configHandler;
    private $versionManager;
    private $contentManager;
    private $userManager;
    private $workspaceManager;

    public function __construct(
        PlatformConfigurationHandler $configHandler,
        VersionManager $versionManager,
        ContentManager $contentManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->configHandler = $configHandler;
        $this->versionManager = $versionManager;
        $this->contentManager = $contentManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * Checks if terms of service functionality is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->configHandler->getParameter('tos.enabled') ?? false;
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
            if ($locale && $allTerms[$locale]) {
                $terms = $allTerms[$locale];
            } elseif ($allTerms[$this->configHandler->getParameter('locales.default')]) {
                $terms = $allTerms[$this->configHandler->getParameter('locales.default')];
            } else {
                $terms = $allTerms[0];
            }
        }

        if ($terms) {
            return $terms['content'];
        }

        return null;
    }

    /**
     * Checks if terms are available in at least one language in a set of translated terms.
     *
     * @param array $translatedTerms an associative array in which each key is a language code
     *                               and each value an associative array with a "content" key
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
     * @param array $translatedTerms an associative array in which each key is a language code
     *                               and each value an associative array with a "content" key
     */
    public function setTermsOfService(array $translatedTerms)
    {
        $terms = $this->contentManager->getContent(['type' => 'termsOfService']);

        if ($terms instanceof Content) {
            $this->contentManager->updateContent($terms, $translatedTerms);
        } else {
            $this->contentManager->createContent($translatedTerms, 'termsOfService');
        }
    }

    public function deleteTermsOfService($locale)
    {
        $termsOfService = $this->contentManager->getContent(['type' => 'termsOfService']);

        if ($termsOfService instanceof Content) {
            $this->contentManager->deleteTranslation($locale, $termsOfService->getId());
        }
    }

    public function sendData()
    {
        $platformUrl = $this->configHandler->getParameter('platform_url');

        if ('OK' === $this->configHandler->getParameter('confirm_send_datas') && !is_null($platformUrl)) {
            $url = $this->configHandler->getParameter('datas_sending_url');
            $name = $this->configHandler->getParameter('name');
            $lang = $this->configHandler->getParameter('locale_language');
            $country = $this->configHandler->getParameter('country');
            $supportEmail = $this->configHandler->getParameter('support_email');
            $version = $this->versionManager->getDistributionVersion();
            $nbNonPersonalWorkspaces = $this->workspaceManager->getNbNonPersonalWorkspaces();
            $nbPersonalWorkspaces = $this->workspaceManager->getNbPersonalWorkspaces();
            $nbUsers = $this->userManager->countEnabledUsers();
            $type = 3;
            $token = $this->configHandler->getParameter('token');

            $postDatas = "name=$name".
                "&url=$platformUrl".
                "&lang=$lang".
                "&country=$country".
                "&email=$supportEmail".
                "&version=$version".
                "&workspaces=$nbNonPersonalWorkspaces".
                "&personal_workspaces=$nbPersonalWorkspaces".
                "&users=$nbUsers".
                "&stats_type=$type".
                "&token=$token";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postDatas);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
        }
    }
}
