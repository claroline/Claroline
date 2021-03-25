<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Plugin;

class PluginSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return Plugin::class;
    }

    /**
     * Serializes a Plugin entity.
     *
     * @return array
     */
    public function serialize(Plugin $plugin, array $options = [])
    {
        return [
            'id' => $plugin->getId(),
            'name' => $plugin->getShortName(),
            'vendor' => $plugin->getVendorName(),
            'bundle' => $plugin->getBundleName(),
        ];
    }

    public function getName()
    {
        return 'plugin';
    }

    /**
     * Deserializes data into a Plugin entity.
     *
     * @param array $data
     *
     * @return Plugin
     */
    public function deserialize($data, Plugin $plugin, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $plugin);
        $this->sipe('name', 'setDisplayName', $data, $plugin);
        $this->sipe('vendor', 'setVendorName', $data, $plugin);
        $this->sipe('bundle', 'setBundleName', $data, $plugin);

        return $plugin;
    }
}
