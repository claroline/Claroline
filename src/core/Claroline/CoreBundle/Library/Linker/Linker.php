<?php

namespace Claroline\CoreBundle\Library\Linker;

use Doctrine\ORM\EntityManager;

class Linker
{
    private $entities;
    /** @ var EntityManager */
    private $em;
            
    public function __construct (EntityManager $em)
    {
        $this->entities[0] = 'Claroline\CoreBundle\Entity\Resource\AbstractResource';
        $this->entities[1] = 'Claroline\CoreBundle\Workspace\AbstractWorkspace';
        $this->em = $em;
    }
    
    public function getLinks()
    {
        foreach($entities as $entity)
        {
            //~
        }
        
        return $links;
    }
}
