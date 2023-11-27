<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\LogBundle\DependencyInjection\Compiler\RegisterLogSubscriberPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineLogBundle extends DistributionPluginBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterLogSubscriberPass());
    }
}
