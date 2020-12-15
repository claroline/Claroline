<?php

namespace Claroline\DevBundle;

use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineDevBundle extends Bundle implements AutoConfigurableInterface
{
    public function supports($environment)
    {
        return 'prod' !== $environment;
    }

    public function getConfiguration($environment)
    {
        return new ConfigurationBuilder();
    }
}
