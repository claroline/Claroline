<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ThemeParameters
{
    /**
     * @ORM\Column(name="theme_mode", nullable=true)
     */
    private ?string $themeMode = null; // auto (null) | light | dark

    /**
     * @ORM\Column(name="font_size", nullable=true)
     */
    private ?string $fontSize = null; // auto (null) | sm (14px) | md (16px) | lg (18px)

    /**
     * @ORM\Column(name="font_weight", nullable=true)
     */
    private ?string $fontWeight = null; // light (300) | normal (400) | medium (500)

    public function getThemeMode(): ?string
    {
        return $this->themeMode;
    }

    public function setThemeMode(?string $themeMode): void
    {
        $this->themeMode = $themeMode;
    }

    public function getFontSize(): ?string
    {
        return $this->fontSize;
    }

    public function setFontSize(?string $fontSize): void
    {
        $this->fontSize = $fontSize;
    }

    public function getFontWeight(): ?string
    {
        return $this->fontWeight;
    }

    public function setFontWeight(?string $fontWeight): void
    {
        $this->fontWeight = $fontWeight;
    }
}
