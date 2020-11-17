<?php

namespace Claroline\HomeBundle\Entity\Type;

use Claroline\HomeBundle\Entity\HomeTab;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTab
{
    /**
     * @ORM\OneToOne(targetEntity="Claroline\HomeBundle\Entity\HomeTab")
     * @ORM\JoinColumn(name="tab_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var HomeTab
     */
    private $tab;

    abstract public static function getType(): string;

    public function getTab()
    {
        return $this->tab;
    }

    public function setTab(HomeTab $tab)
    {
        $this->tab = $tab;
    }
}
