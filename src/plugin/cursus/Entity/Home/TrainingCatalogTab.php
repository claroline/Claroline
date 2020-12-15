<?php

namespace Claroline\CursusBundle\Entity\Home;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\HomeBundle\Entity\Type\AbstractTab;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab_training_catalog")
 */
class TrainingCatalogTab extends AbstractTab
{
    use Id;

    public static function getType(): string
    {
        return 'training_catalog';
    }
}
