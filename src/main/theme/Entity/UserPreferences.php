<?php

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\AbstractUserPreferences;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_theme_user_preferences')]
#[ORM\Entity]
class UserPreferences extends AbstractUserPreferences
{
    use ThemeParameters;

    #[ORM\Column(name: 'theme', nullable: true)]
    private ?string $theme = null;

    #[ORM\Column(name: 'list_display', nullable: true)]
    private ?string $listDisplay = null;

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): void
    {
        $this->theme = $theme;
    }

    public function getListDisplay(): ?string
    {
        return $this->listDisplay;
    }

    public function setListDisplay(?string $display): void
    {
        $this->listDisplay = $display;
    }
}
