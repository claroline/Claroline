<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SimpleWidget.
 *
 * Permits to display a simple HTML content text.
 */
#[ORM\Table(name: 'claro_widget_simple')]
#[ORM\Entity]
class SimpleWidget extends AbstractWidget
{
    /**
     * The HTML content of the widget.
     *
     *
     * @var string
     */
    #[ORM\Column(type: Types::TEXT)]
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
