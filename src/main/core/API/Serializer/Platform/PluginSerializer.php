<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\PluginManager;

class PluginSerializer
{
    use SerializerTrait;

    /** @var PluginManager */
    private $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function getClass()
    {
        return Plugin::class;
    }

    public function getName()
    {
        return 'plugin';
    }

    /**
     * Serializes a Plugin entity.
     */
    public function serialize(Plugin $plugin, array $options = []): array
    {
        return [
            'id' => $plugin->getId(),
            'name' => $plugin->getShortName(),
            'meta' => [
                'version' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->getVersion($plugin) : null,
                'vendor' => $plugin->getVendorName(),
                'bundle' => $plugin->getBundleName(),
            ],
            'ready' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->isReady($plugin) : false,
            'enabled' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->isLoaded($plugin) : false,
            'locked' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->isLocked($plugin) : true,

            'requirements' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->getRequirements($plugin) : [],
            'requiredBy' => class_exists($plugin->getBundleFQCN()) ? $this->pluginManager->getRequiredBy($plugin) : [],
        ];
    }

    /**
     * Deserializes data into a Plugin entity.
     */
    public function deserialize(array $data, Plugin $plugin, array $options = []): Plugin
    {
        $this->sipe('id', 'setUuid', $data, $plugin);
        $this->sipe('name', 'setDisplayName', $data, $plugin);
        $this->sipe('vendor', 'setVendorName', $data, $plugin);
        $this->sipe('bundle', 'setBundleName', $data, $plugin);

        return $plugin;
    }
}
