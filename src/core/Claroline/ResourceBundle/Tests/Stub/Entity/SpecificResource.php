<?php

namespace Claroline\ResourceBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\ResourceBundle\Entity\Resource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_specific_resource")
 */
class SpecificResource extends Resource
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;
    
    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}