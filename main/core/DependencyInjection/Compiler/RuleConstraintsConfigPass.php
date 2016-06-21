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
use Symfony\Component\DependencyInjection\Reference;

class RuleConstraintsConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $ruleValidatorServiceKey = 'claroline.rule.validator';

        if (false === $container->hasDefinition($ruleValidatorServiceKey)) {
            return;
        }

        $ruleValidator = $container->getDefinition($ruleValidatorServiceKey);

        foreach ($container->findTaggedServiceIds('claroline.rule.constraint') as $id => $attributes) {
            $ruleValidator->addMethodCall('addConstraint', array(new Reference($id)));
        }
    }
}
