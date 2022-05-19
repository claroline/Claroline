<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceTypeSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Serializes platform parameters used for client rendering.
 */
class ClientSerializer
{
    /** @var string */
    private $env;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RequestStack */
    private $requestStack;

    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var PlatformManager */
    private $platformManager;

    /** @var VersionManager */
    private $versionManager;

    /** @var PluginManager */
    private $pluginManager;

    /** @var UserManager */
    private $userManager;

    /** @var ResourceTypeSerializer */
    private $resourceTypeSerializer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        string $env,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        PlatformManager $platformManager,
        VersionManager $versionManager,
        PluginManager $pluginManager,
        UserManager $userManager,
        ResourceTypeSerializer $resourceTypeSerializer
    ) {
        $this->env = $env;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->om = $om;
        $this->config = $config;
        $this->platformManager = $platformManager;
        $this->versionManager = $versionManager;
        $this->pluginManager = $pluginManager;
        $this->userManager = $userManager;
        $this->resourceTypeSerializer = $resourceTypeSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName()
    {
        return 'client';
    }

    /**
     * Serializes required information for FrontEnd rendering.
     */
    public function serialize()
    {
        $logo = null;
        if ($this->config->getParameter('logo')) {
            $logo = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $this->config->getParameter('logo'),
            ]);
        }

        $data = [
            'logo' => $logo ? $logo->getUrl() : null,
            'name' => $this->config->getParameter('name'),
            'secondaryName' => $this->config->getParameter('secondary_name'),
            'description' => null, // the one for the current locale
            'version' => $this->versionManager->getCurrent(),
            'environment' => $this->env,
            'helpUrl' => $this->config->getParameter('help_url'),
            'selfRegistration' => $this->config->getParameter('registration.self') && !$this->userManager->hasReachedLimit(),
            'community' => $this->config->getParameter('community'),
            'serverUrl' => $this->platformManager->getUrl(),
            'locale' => $this->serializeLocale(),
            'display' => [ // TODO : to move
                'breadcrumb' => $this->config->getParameter('display.breadcrumb'),
            ],
            'restrictions' => $this->config->getParameter('restrictions'),
            'openGraph' => [
                'enabled' => $this->config->getParameter('enable_opengraph'),
            ],
            'home' => $this->config->getParameter('home'),
            'resources' => [ // TODO : maybe no longer needed here
                'types' => array_map(function (ResourceType $resourceType) {
                    return $this->resourceTypeSerializer->serialize($resourceType);
                }, $this->om->getRepository(ResourceType::class)->findAll()),
                'softDelete' => $this->config->getParameter('resource.soft_delete'),
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
            'privacy' => $this->config->getParameter('privacy'),
            'pricing' => $this->config->getParameter('pricing'),
            'plugins' => $this->pluginManager->getEnabled(),
        ];

        $event = new GenericDataEvent();
        $this->eventDispatcher->dispatch($event, 'claroline_populate_client_config');
        $data = array_merge_recursive($data, $event->getResponse() ?? []);

        return $data;
    }

    private function serializeLocale()
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
