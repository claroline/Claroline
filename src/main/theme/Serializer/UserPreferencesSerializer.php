<?php

namespace Claroline\ThemeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\UserPreferences;

class UserPreferencesSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return UserPreferences::class;
    }

    public function serialize(UserPreferences $preferences): array
    {
        return [
            'theme' => $preferences->getTheme(),
            'themeMode' => $preferences->getThemeMode(),
            'fontSize' => $preferences->getFontSize(),
            'fontWeight' => $preferences->getFontWeight(),
        ];
    }

    public function deserialize(array $data, UserPreferences $preferences): UserPreferences
    {
        $this->sipe('theme', 'setTheme', $data, $preferences);
        $this->sipe('themeMode', 'setThemeMode', $data, $preferences);
        $this->sipe('fontSize', 'setFontSize', $data, $preferences);
        $this->sipe('fontWeight', 'setFontWeight', $data, $preferences);

        return $preferences;
    }
}
