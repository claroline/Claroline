<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

interface AutoInstallableInterface
{
    public function supports($environment);

    public function getConfiguration($environment);
}
