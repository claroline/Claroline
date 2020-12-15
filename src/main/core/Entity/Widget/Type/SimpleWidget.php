<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Doctrine\ORM\Mapping as ORM;

/**
 * SimpleWidget.
 *
 * Permits to display a simple HTML content text.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_simple")
 */
class SimpleWidget extends AbstractWidget
{
    /**
     * The HTML content of the widget.
     *
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $content;

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
