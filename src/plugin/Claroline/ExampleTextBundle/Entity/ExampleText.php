<?php

//Your entity must extends abstract resource.

namespace Claroline\ExampleTextBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_example_text")
 */
class ExampleText extends AbstractResource
{
    /**
     * @ORM\Column(type="string")
     */
    private $text;

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
}
