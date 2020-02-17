<?php

namespace Claroline\AppBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformManager
{
    /** @var Request */
    private $request;

    /** @var PlatformConfigurationHandler */
    private $config;

    /**
     * PlatformManager constructor.
     *
     * @param RequestStack                 $requestStack
     * @param PlatformConfigurationHandler $config
     */
    public function __construct(
        RequestStack $requestStack,
        PlatformConfigurationHandler $config
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->config = $config;
    }

    /**
     * Gets the platform URL.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->config->getParameter('internet.platform_url');
        if (empty($url)) {
            // add protocol
            $url = $this->request->isSecure() || $this->config->getParameter('ssl.enabled') ? 'https://' : 'http://';

            // add host
            $url .= $this->config->getParameter('internet.domain_name') ? $this->config->getParameter('internet.domain_name') : $this->request->getHost();

            // add path if any
            $url .= '/'.trim($this->request->getBasePath(), '/');
        }

        return trim($url, '/');
    }
}
