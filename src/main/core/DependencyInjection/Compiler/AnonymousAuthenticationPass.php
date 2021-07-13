<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AnonymousAuthenticationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // replace standard symfony listener in order to populate AnonymousToken with a role `ROLE_ANONYMOUS`
        $definition = $container->findDefinition('security.authentication.listener.anonymous');
        $definition->setClass('Claroline\CoreBundle\Listener\AnonymousAuthenticationListener');
    }
}
