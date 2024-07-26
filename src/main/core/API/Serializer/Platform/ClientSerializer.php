<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceTypeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Serializes platform parameters used for client rendering.
 */
class ClientSerializer
{
    public function __construct(
        private readonly string $env,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly PlatformManager $platformManager,
        private readonly VersionManager $versionManager,
        private readonly PluginManager $pluginManager,
        private readonly LocaleManager $localeManager,
        private readonly UserManager $userManager,
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

        $data = [
            'logo' => $this->config->getParameter('logo'),
            'name' => $this->config->getParameter('name'),
            'version' => $this->versionManager->getCurrent(),
            'environment' => $this->env,
            'helpUrl' => $this->config->getParameter('help_url'),
            'selfRegistration' => $this->config->getParameter('registration.self') && !$this->userManager->hasReachedLimit(),
            'community' => $this->config->getParameter('community'),
            'serverUrl' => $this->platformManager->getUrl(),
            'locale' => [
                'default' => $this->localeManager->getDefault(),
                'current' => $this->localeManager->getUserLocale($request),
                'available' => $this->localeManager->getEnabledLocales(),
            ],
            'restrictions' => $this->config->getParameter('restrictions'),
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

        $event = new GenericDataEvent();
        $this->eventDispatcher->dispatch($event, 'claroline_populate_client_config');

        return array_merge($data, $event->getResponse() ?? []);
    }
}
