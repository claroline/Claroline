<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

interface ConfigurationProviderInterface
{
    public function suggestConfigurationFor(Bundle $bundle, $environment);
}
