<?php

namespace Claroline\AppBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\VersionManager;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformManager
{
    public function __construct(
        private readonly string $env,
        private readonly RequestStack $requestStack,
        private readonly VersionManager $versionManager,
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public function getEnv(): string
    {
        return $this->env;
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
            $url = $request && $request->isSecure() ? 'https://' : 'http://';

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

    public function getVersion(): string
    {
        return $this->versionManager->getCurrent();
    }

    public function hasParameter(string $parameter): bool
    {
        return $this->config->hasParameter($parameter);
    }

    public function getParameter(string $parameter): mixed
    {
        return $this->config->getParameter($parameter);
    }
}
