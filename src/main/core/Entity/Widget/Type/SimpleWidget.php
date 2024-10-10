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
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
