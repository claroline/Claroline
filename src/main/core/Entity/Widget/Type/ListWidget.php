<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

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

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxResults;

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }
}
