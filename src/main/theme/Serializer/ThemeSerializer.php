<?php

namespace Claroline\ThemeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\Theme;

class ThemeSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Theme::class;
    }

    public function serialize(Theme $theme): array
    {
        return [
            'id' => $theme->getUuid(),
            'name' => $theme->getName(),
            'normalizedName' => $theme->getNormalizedName(),
            'logo' => $theme->getLogo(),
            /*'title' => $theme->getTitle(),
            'subtitle' => $theme->getSubtitle(),*/
            'themeMode' => $theme->getThemeMode(),
            'fontSize' => $theme->getFontSize(),
            'fontWeight' => $theme->getFontWeight(),
        ];
    }

    public function deserialize(array $data, Theme $theme): Theme
    {
        // meta
        $this->sipe('id', 'setUuid', $data, $theme);
        $this->sipe('name', 'setName', $data, $theme);

        // params
        $this->sipe('logo', 'setLogo', $data, $theme);
        /*$this->sipe('title', 'setTitle', $data, $theme);
        $this->sipe('subtitle', 'setSubtitle', $data, $theme);*/

        $this->sipe('themeMode', 'setThemeMode', $data, $theme);
        $this->sipe('fontSize', 'setFontSize', $data, $theme);
        $this->sipe('fontWeight', 'setFontWeight', $data, $theme);

        return $theme;
    }
}
