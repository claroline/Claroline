<?php

namespace Claroline\ThemeBundle\Serializer;

use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\Theme;

class ThemeSerializer
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * ThemeSerializer constructor.
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        UserSerializer $userSerializer
    ) {
        $this->config = $config;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'theme';
    }

    /**
     * Serializes a Theme entity for the JSON api.
     *
     * @param Theme $theme - the theme to serialize
     *
     * @return array - the serialized representation of the theme
     */
    public function serialize(Theme $theme)
    {
        return [
            'id' => $theme->getUuid(),
            'name' => $theme->getName(),
            'normalizedName' => str_replace(' ', '-', strtolower($theme->getName())),
            'current' => $theme->getNormalizedName() === str_replace(' ', '-', strtolower($this->config->getParameter('theme'))),
            'meta' => [
                'description' => $theme->getDescription(),
                'default' => $theme->isDefault(),
                'enabled' => $theme->isEnabled(),
                'custom' => $theme->isCustom(),
                'plugin' => $theme->getPlugin() ? $theme->getPlugin()->getShortName() : null,
                'creator' => $theme->getUser() ? $this->userSerializer->serialize($theme->getUser()) : null,
            ],
            'parameters' => [
                'extendDefault' => $theme->isExtendingDefault(),
            ],
        ];
    }

    /**
     * Deserializes JSON api data into a Theme entity.
     *
     * @param array $data  - the data to deserialize
     * @param Theme $theme - the theme entity to update
     *
     * @return Theme - the updated theme entity
     */
    public function deserialize(array $data, Theme $theme = null)
    {
        $theme = $theme ?: new Theme();

        $theme->setName($data['name']);

        // todo : update other themes props

        return $theme;
    }
}
