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
     * @ORM\OneToOne(targetEntity="Claroline\HomeBundle\Entity\HomeTab", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="tab_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?HomeTab $tab = null;

    abstract public static function getType(): string;

    public function getTab(): HomeTab
    {
        return $this->tab;
    }

    public function setTab(HomeTab $tab): void
    {
        $this->tab = $tab;
    }
}
