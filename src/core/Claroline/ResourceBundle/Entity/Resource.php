<?php

namespace Claroline\ResourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource")
 */
class Resource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length="255")
     */
    protected $content; // TEMPORARY -> will use @extendable to define resource types
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}