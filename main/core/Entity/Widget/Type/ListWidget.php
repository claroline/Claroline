<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Claroline\AppBundle\Entity\Parameters\ListParameters;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListWidget.
 *
 * Permits to render an arbitrary list of data.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_list")
 */
class ListWidget extends AbstractWidget
{
    use ListParameters;
}
