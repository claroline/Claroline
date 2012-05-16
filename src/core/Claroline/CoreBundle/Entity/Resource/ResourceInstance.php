<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/*
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_instance")
 */

class ResourceInstance
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\generatedValue(strategy="AUTO")
     */
     protected $id;
     
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Repository")
     */
    private $repository;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource")
     */
    private $resource;    
    
    public function getRepository()
    {
        return $this->repository;
    }
    
    public function getCommande()
    {
        return $this->resource;
    }
}