<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\PluginBundle\Entity\Plugin;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_extension")
 */
class Extension extends Plugin
{
}