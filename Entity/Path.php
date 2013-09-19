<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace as Workspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
/**
 * Path
 *
 * @ORM\Table(name="innova_path")
* @ORM\Entity
 */
class Path extends AbstractResource
{
    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=255)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text")
     */
    private $path;
   
    /**
     * Set path
     *
     * @param string $path
     * @return Path
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return Path
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid()
    {
        return $this->uuid;
    }
 
}
