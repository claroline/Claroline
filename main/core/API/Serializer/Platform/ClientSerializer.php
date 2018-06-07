<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceTypeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Icon\ResourceIconItemFilename;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\VersionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Serializes platform parameters used for client rendering.
 *
 * @DI\Service("claroline.serializer.platform_client")
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

    /** @var VersionManager */
    private $versionManager;

    /** @var PluginManager */
    private $pluginManager;

    /** @var IconSetManager */
    private $iconManager;

    /** @var ResourceTypeSerializer */
    private $resourceTypeSerializer;

    /**
     * ClientSerializer constructor.
     *
     * @DI\InjectParams({
     *     "env"                    = @DI\Inject("%kernel.environment%"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "requestStack"           = @DI\Inject("request_stack"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "config"                 = @DI\Inject("claroline.config.platform_config_handler"),
     *     "versionManager"         = @DI\Inject("claroline.manager.version_manager"),
     *     "pluginManager"          = @DI\Inject("claroline.manager.plugin_manager"),
     *     "iconManager"            = @DI\Inject("claroline.manager.icon_set_manager"),
     *     "resourceTypeSerializer" = @DI\Inject("claroline.serializer.resource_type")
     * })
     *
     * @param string                       $env,
     * @param TokenStorageInterface        $tokenStorage
     * @param RequestStack                 $requestStack
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $config
     * @param VersionManager               $versionManager
     * @param PluginManager                $pluginManager
     * @param IconSetManager               $iconManager
     * @param ResourceTypeSerializer       $resourceTypeSerializer
     */
    public function __construct(
        $env,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        VersionManager $versionManager,
        PluginManager $pluginManager,
        IconSetManager $iconManager,
        ResourceTypeSerializer $resourceTypeSerializer
    ) {
        $this->env = $env;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->om = $om;
        $this->config = $config;
        $this->versionManager = $versionManager;
        $this->pluginManager = $pluginManager;
        $this->iconManager = $iconManager;
        $this->resourceTypeSerializer = $resourceTypeSerializer;
    }

    /**
     * Serializes required information for FrontEnd rendering.
     */
    public function serialize()
    {
        $request = $this->requestStack->getCurrentRequest();

        $currentUser = null;
        if (!empty($this->tokenStorage->getToken())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
        }

        // retrieve the current platform locale
        $locale = $this->config->getParameter('locale_language');
        if ($currentUser instanceof User) {
            // Get the locale for the logged user
            $locale = $currentUser->getLocale();
        } elseif (!empty($this->config->getParameter('locales')) && array_key_exists($request->getLocale(), $this->config->getParameter('locales'))) {
            // The current request locale is implemented so we use it
            $locale = $request->getLocale();
        }

        $icons = $this->iconManager->getIconSetIconsByType(
            $this->iconManager->getActiveResourceIconSet()
        );

        return [
            'name' => $this->config->getParameter('name'),
            'description' => null, // the one for the current locale
            'version' => $this->versionManager->getDistributionVersion(),
            'help' => $this->config->getParameter('help_url'),
            'environment' => $this->env,
            'asset' => null,
            'server' => [
                'protocol' => $request->isSecure() || $this->config->getParameter('ssl_enabled') ? 'https' : 'http',
                'host' => $this->config->getParameter('domain_name') ? $this->config->getParameter('domain_name') : $request->getHost(),
                'path' => $request->getBasePath(),
            ],
            'theme' => [
                'name' => $this->config->getParameter('theme'),
                'icons' => array_map(function (ResourceIconItemFilename $icon) {
                    return [
                        'mimeTypes' => $icon->getMimeTypes(),
                        'url' => $icon->getRelativeUrl(),
                    ];
                }, array_values(array_merge(
                    $icons->getDefaultIcons()->getAllIcons(),
                    $icons->getSetIcons()->getAllIcons()
                ))),
            ],
            'locale' => [
                'current' => $locale,
                'available' => $this->config->getParameter('locales'),
            ],
            'openGraph' => [
                'enabled' => $this->config->getParameter('enable_opengraph'),
            ],
            'resourceTypes' => array_map(function (ResourceType $resourceType) {
                return $this->resourceTypeSerializer->serialize($resourceType);
            }, $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll()),
            'plugins' => $this->pluginManager->getEnabled(true),
        ];
    }
}
