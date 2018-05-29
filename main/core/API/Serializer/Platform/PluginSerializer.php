<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Plugin;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.plugin")
 * @DI\Tag("claroline.serializer")
 */
class PluginSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Plugin';
    }

    /**
     * Serializes a Plugin entity.
     *
     * @param Plugin $plugin
     * @param array $options
     *
     * @return array
     */
    public function serialize(Plugin $plugin, array $options = [])
    {
        return [
            'id' => $plugin->getId(),
            'name' => $plugin->getDisplayName(),
            'vendor' => $plugin->getVendorName(),
            'bundle' => $plugin->getBundleName(),
        ];
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param array  $data
     * @param Plugin $plugin
     * @param array  $options
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
