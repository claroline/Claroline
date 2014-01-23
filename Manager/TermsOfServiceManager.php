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
    private $content;
    private $request;

    /**
     * @InjectParams({
     *     "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "content"        = @Inject("claroline.manager.content_manager"),
     *     "requestStack"   = @Inject("request_stack")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        ContentManager $content,
        RequestStack $requestStack
    )
    {
        $this->content = $content;
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

    public function getTermsOfService($translations = true)
    {
        if ($translations) {
            return $this->content->getTranslatedContent(array('type' => 'termsOfService'));
        }

        return $this->content->getContent(array('type' => 'termsOfService'));
    }

    public function setTermsOfService($translatedContent)
    {
        $termsOfService = $this->content->getContent(array('type' => 'termsOfService'));

        if ($termsOfService instanceof Content) {
            $this->content->updateContent($termsOfService, $translatedContent);
        } else {
            $this->content->createContent($translatedContent, 'termsOfService');
        }
    }

    public function deleteTermsOfService($locale)
    {
        $termsOfService = $this->content->getContent(array('type' => 'termsOfService'));

        if ($termsOfService instanceof Content) {
            $this->content->deleteTranslation($locale, $termsOfService->getId());
        }
    }
}
