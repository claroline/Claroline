<?php

namespace Claroline\ResourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\ResourceBundle\Entity\Resource;
use Claroline\CommonBundle\Annotation\ORM as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_text")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class Text extends Resource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $type;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $content;
    
    public function getType()
    {
        return $this->type;
    }

    // TODO : force type to be in a list of predefined mimetypes ?
    public function setType($type)
    {
        $this->type = $type;
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