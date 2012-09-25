<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is only an AbstractResource sub-type, with no additional attributes.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro_directory")
 */
class Directory extends AbstractResource
{
}