<?php

namespace Claroline\AppBundle\Entity\Parameters;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractParameters
{
    use Id;
}
