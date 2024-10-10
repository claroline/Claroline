<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Parameters\ListParameters;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListWidget.
 *
 * Permits to render an arbitrary list of data.
 */
#[ORM\Table(name: 'claro_widget_list')]
#[ORM\Entity]
class ListWidget extends AbstractWidget
{
    use ListParameters;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxResults = null;

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function setMaxResults(?int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
