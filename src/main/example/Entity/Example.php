<?php

namespace Claroline\ExampleBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\ExampleBundle\Finder\ExampleType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_example_example')]
#[ORM\Entity]
#[CrudEntity(finderClass: ExampleType::class)]
class Example implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use CreatedAt;
    use UpdatedAt;
    use Creator;
    use Thumbnail;
    use Poster;

    // + Custom props

    public function __construct()
    {
        $this->refreshUuid();
    }

    public static function getIdentifiers(): array
    {
        return [];
    }
}
