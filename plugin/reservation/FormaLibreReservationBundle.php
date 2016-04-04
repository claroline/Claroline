<?php

namespace FormaLibre\ReservationBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use FormaLibre\ReservationBundle\Installation\AdditionalInstaller;

class FormaLibreReservationBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'reservation');
    }

    public function hasMigrations()
    {
        return true;
    }
}
