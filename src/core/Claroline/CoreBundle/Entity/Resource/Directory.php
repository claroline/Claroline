<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DirectoryRepository")
 * @ORM\Table(name="claro_directory")
 */
class Directory extends AbstractResource
{
    
}