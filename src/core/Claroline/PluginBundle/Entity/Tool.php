<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\PluginBundle\Repository\PluginRepository")
 */
class Tool extends AbstractPlugin
{
}