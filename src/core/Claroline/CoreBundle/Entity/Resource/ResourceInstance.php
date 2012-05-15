<?php

/*
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DirectoryRepository")
 * @ORM\Table(name="claro_directory")
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