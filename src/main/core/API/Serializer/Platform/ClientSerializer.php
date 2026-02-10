<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceTypeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Serializes platform parameters used for client rendering.
 */
class ClientSerializer
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly PlatformManager $platformManager,
        private readonly PluginManager $pluginManager,
        private readonly LocaleManager $localeManager,
        private readonly ResourceTypeSerializer $resourceTypeSerializer
    ) {
    }

    public function getName(): string
    {
        return 'client';
    }

    /**
     * Serializes required information for FrontEnd rendering.
     */
    public function serialize(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        return [
            // 'logo' => $this->config->getParameter('logo'),
            'name' => $this->config->getParameter('name'),
            'description' => $this->config->getParameter('meta.description'),
            'version' => $this->platformManager->getVersion(),
            'environment' => $this->platformManager->getEnv(),
            'help' => $this->config->getParameter('help_url'),
            'contact' => $this->config->getParameter('help.support_email'),
            'selfRegistration' => $this->config->getParameter('registration.self'),
            'community' => $this->config->getParameter('community'),
            'serverUrl' => $this->platformManager->getUrl(),
            'locale' => [
                'current' => $this->localeManager->getUserLocale($request),
                'available' => $this->localeManager->getAvailableLocales(),
            ],
            'richTextScript' => $this->config->getParameter('rich_text_script'),
            'resources' => [
                'types' => array_map(function (ResourceType $resourceType) {
                    return $this->resourceTypeSerializer->serialize($resourceType);
                }, $this->om->getRepository(ResourceType::class)->findAll()),
            ],
            'pricing' => $this->config->getParameter('pricing'),
            'plugins' => $this->pluginManager->getEnabled(),
            'uploadMaxFilesize' => UploadedFile::getMaxFilesize(),
        ];
    }
}
