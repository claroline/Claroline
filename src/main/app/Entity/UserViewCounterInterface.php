<?php

namespace Claroline\AppBundle\Entity;

interface UserViewCounterInterface
{
    public function getViews(): int;

    public function addView(): void;
}
