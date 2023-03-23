<?php

namespace Claroline\ExampleBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_example_example")
 */
class Example
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
}
