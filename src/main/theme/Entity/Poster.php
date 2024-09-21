<?php

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_theme_poster')]
#[ORM\Entity]
class Poster
{
    use Id;
    use Uuid;

    private ?string $image;

    public function __construct()
    {
        $this->refreshUuid();
    }
}
