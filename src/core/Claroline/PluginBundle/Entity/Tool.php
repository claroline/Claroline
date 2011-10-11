<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\PluginBundle\Repository\PluginRepository")
 * @ORM\Table(name="claro_tool")
 */
class Tool extends Plugin
{
}