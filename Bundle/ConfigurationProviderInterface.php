<?php

namespace Claroline\KernelBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

interface ConfigurationProviderInterface
{
    public function suggestConfigurationFor(Bundle $bundle, $environment);
}
