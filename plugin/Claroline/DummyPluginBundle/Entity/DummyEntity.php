<?php

namespace Claroline\DummyPluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dummy_entity")
 */
class DummyEntity
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
    protected $name;

    public function getId()
    {
        return $this->id();
    }
}