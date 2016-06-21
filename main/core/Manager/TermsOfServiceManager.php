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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.common.terms_of_service_manager")
 */
class TermsOfServiceManager
{
    private $configHandler;
    private $container;
    private $contentManager;
    private $isActive;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "configHandler"    = @DI\Inject("claroline.config.platform_config_handler"),
     *     "container"        = @DI\Inject("service_container"),
     *     "contentManager"   = @DI\Inject("claroline.manager.content_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        ContainerInterface $container,
        ContentManager $contentManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->configHandler = $configHandler;
        $this->container = $container;
        $this->contentManager = $contentManager;
        $this->isActive = $configHandler->getParameter('terms_of_service');
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
     * @param array $translatedTerms An associative array in which each key is a language code
     *                               and each value an associative array with a "content" key.
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
     * @param array $translatedTerms An associative array in which each key is a language code
     *                               and each value an associative array with a "content" key.
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

    public function sendDatas()
    {
        $platformUrl = $this->configHandler->getParameter('platform_url');

        if ($this->configHandler->getParameter('confirm_send_datas') === 'OK' && !is_null($platformUrl)) {
            $url = $this->configHandler->getParameter('datas_sending_url');
            $name = $this->configHandler->getParameter('name');
            $lang = $this->configHandler->getParameter('locale_language');
            $country = $this->configHandler->getParameter('country');
            $supportEmail = $this->configHandler->getParameter('support_email');
            $version = $this->getCoreBundleVersion();
            $nbNonPersonalWorkspaces = $this->workspaceManager->getNbNonPersonalWorkspaces();
            $nbPersonalWorkspaces = $this->workspaceManager->getNbPersonalWorkspaces();
            $nbUsers = $this->userManager->getCountAllEnabledUsers();
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

    private function getCoreBundleVersion()
    {
        $ds = DIRECTORY_SEPARATOR;
        $version = '-';
        $installedFile = $this->container->getParameter('kernel.root_dir').
            $ds.'..'.$ds.'vendor'.$ds.'composer'.$ds.'installed.json';
        $jsonString = file_get_contents($installedFile);
        $bundles = json_decode($jsonString, true);

        foreach ($bundles as $bundle) {
            if (isset($bundle['name']) && $bundle['name'] === 'claroline/core-bundle') {
                $version = $bundle['version'];
                break;
            }
        }

        return $version;
    }
}
