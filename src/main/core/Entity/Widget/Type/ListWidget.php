<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Claroline\AppBundle\Entity\Parameters\ListParameters;
use Doctrine\DBAL\Types\Types;
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

    /**
     * Filter the list by the current context instance or not.
     */
    #[ORM\Column(name: 'all_contexts', type: Types::BOOLEAN)]
    private bool $all = false;

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

    public function hasAll(): bool
    {
        return $this->all;
    }

    public function setAll(bool $all): void
    {
        $this->all = $all;
    }
}
