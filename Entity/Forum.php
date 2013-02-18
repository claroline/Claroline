<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\ForumRepository")
 */
class Forum extends AbstractResource
{

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }
}