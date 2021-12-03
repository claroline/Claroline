<?php

namespace Claroline\AppBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformManager
{
    /** @var RequestStack */
    private $requestStack;
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        RequestStack $requestStack,
        PlatformConfigurationHandler $config
    ) {
        $this->requestStack = $requestStack;
        $this->config = $config;
    }

    /**
     * Gets the platform URL.
     */
    public function getUrl(): ?string
    {
        $url = $this->config->getParameter('internet.platform_url');
        if (empty($url)) {
            // we will try to deduce the current platform URL based on the request if any
            $request = $this->requestStack->getCurrentRequest();

            // add protocol
            $url = ($request && $request->isSecure()) || $this->config->getParameter('ssl.enabled') ? 'https://' : 'http://';

            // add host
            if ($this->config->getParameter('internet.domain_name')) {
                $url .= $this->config->getParameter('internet.domain_name');
            } elseif ($request) {
                $url .= $request->getHost();
            }

            // add path if any
            if ($request) {
                $url .= '/'.trim($request->getBasePath(), '/');
            }
        }

        return trim($url, '/');
    }
}
