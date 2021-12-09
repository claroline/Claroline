<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\DependencyInjection\Compiler\AnonymousAuthenticationPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\GeoipPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\MailingConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\MessengerConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\PlatformConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\SessionConfigPass;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineCoreBundle extends DistributionPluginBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PlatformConfigPass());
        $container->addCompilerPass(new AnonymousAuthenticationPass());
        $container->addCompilerPass(new MailingConfigPass());
        $container->addCompilerPass(new SessionConfigPass());
        $container->addCompilerPass(new MessengerConfigPass());
        $container->addCompilerPass(new GeoipPass());
    }
}
