<?php

namespace FormaLibre\ReservationBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class FormaLibreReservationBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return true;
    }
}
