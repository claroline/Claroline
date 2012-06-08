<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DirectoryRepository")
 * @ORM\Table(name="claro_directory")
 */

/*
 * This class can be considered as an AbstractClassResource instantiation:
 * This is a Resource with a name and some children. Nothing else.
 */
class Directory extends AbstractResource
{
    
}