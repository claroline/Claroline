<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceTypeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Serializes platform parameters used for client rendering.
 */
class ClientSerializer
{
    public function __construct(
        private readonly string $env,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly PlatformManager $platformManager,
        private readonly VersionManager $versionManager,
        private readonly PluginManager $pluginManager,
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
        $data = [
            'logo' => $this->config->getParameter('logo'),
            'name' => $this->config->getParameter('name'),
            'description' => null, // the one for the current locale
            'version' => $this->versionManager->getCurrent(),
            'environment' => $this->env,
            'helpUrl' => $this->config->getParameter('help_url'),
            'selfRegistration' => $this->config->getParameter('registration.self') && !$this->userManager->hasReachedLimit(),
            'community' => $this->config->getParameter('community'),
            'serverUrl' => $this->platformManager->getUrl(),
            'locale' => $this->serializeLocale(),
            /*'display' => [ // TODO : to move
                'breadcrumb' => $this->config->getParameter('display.breadcrumb'),
            ],*/
            'restrictions' => $this->config->getParameter('restrictions'),
            'richTextScript' => $this->config->getParameter('rich_text_script'),
            'home' => $this->config->getParameter('home'),
            'resources' => [ // TODO : find a better way to store and expose this
                'types' => array_map(function (ResourceType $resourceType) {
                    return $this->resourceTypeSerializer->serialize($resourceType);
                }, $this->om->getRepository(ResourceType::class)->findAll()),
            ],
            'desktop' => [ // TODO : find a better way to store and expose this
                'defaultTool' => $this->config->getParameter('desktop.default_tool'),
                'showProgression' => $this->config->getParameter('desktop.show_progression'),
                'menu' => $this->config->getParameter('desktop.menu'),
            ],
            'admin' => [ // TODO : find a better way to store and expose this
                'defaultTool' => $this->config->getParameter('admin.default_tool'),
                'menu' => $this->config->getParameter('admin.menu'),
            ],
            'pricing' => $this->config->getParameter('pricing'),
            'plugins' => $this->pluginManager->getEnabled(),
            'uploadMaxFilesize' => UploadedFile::getMaxFilesize(),
        ];

        $event = new GenericDataEvent();
        $this->eventDispatcher->dispatch($event, 'claroline_populate_client_config');

        return array_merge_recursive($data, $event->getResponse() ?? []);
    }

    private function serializeLocale(): array
    {
        // TODO : there is a method in LocaleManager to do that. Reuse it
        $request = $this->requestStack->getCurrentRequest();

        $currentUser = null;
        if (!empty($this->tokenStorage->getToken())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
        }

        // retrieve the current platform locale
        $defaultLocale = $this->config->getParameter('locales.default');
        if ($currentUser instanceof User) {
            // Get the locale for the logged user
            $locale = $currentUser->getLocale();
        } elseif (!empty($this->config->getParameter('locales.available')) && array_key_exists($request->getLocale(), $this->config->getParameter('locales.available'))) {
            // The current request locale is implemented so we use it
            $locale = $request->getLocale();
        }

        return [
            'default' => $defaultLocale,
            'current' => $locale ?? $defaultLocale,
            'available' => $this->config->getParameter('locales.available'),
        ];
    }
}
