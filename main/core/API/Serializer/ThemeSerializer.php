<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.theme")
 * @DI\Tag("claroline.serializer")
 */
class ThemeSerializer
{
    /** @var ThemeManager */
    private $themeManager;

    /**
     * ThemeSerializer constructor.
     *
     * @DI\InjectParams({
     *     "themeManager" = @DI\Inject("claroline.manager.theme_manager")
     * })
     *
     * @param ThemeManager $themeManager
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
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
            'current' => $this->themeManager->isCurrentTheme($theme),
            'meta' => [
                'description' => $theme->getDescription(),
                'default' => $theme->isDefault(),
                'enabled' => $theme->isEnabled(),
                'custom' => $theme->isCustom(),
                'plugin' => $theme->getPlugin() ? $theme->getPlugin()->getDisplayName() : null,
            ],
            'parameters' => [
                'extendDefault' => $theme->isExtendingDefault(),
            ],
        ];
    }
}
