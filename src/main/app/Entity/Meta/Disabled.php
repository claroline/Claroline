<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Disabled
{
    /**
     * @ORM\Column(name="is_disabled", type="boolean")
     */
    protected bool $disabled = false;

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
