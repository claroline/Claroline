<?php

namespace Innova\PathBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace as Workspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Path
 *
 * @ORM\Table(name="innova_path")
* @ORM\Entity
 */
class Step extends AbstractResource
{
   /**
     * @var string
     *
     * @ORM\Column(name="title", type="text")
     */
    private $title;
   
    /**
     * Set title
     *
     * @param string $title
     * @return Path
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
   

 
}
