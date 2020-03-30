<?php

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\Parameters\AbstractParameters;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_parameters_appearance")
 */
class AppearanceParameters extends AbstractParameters
{
}
