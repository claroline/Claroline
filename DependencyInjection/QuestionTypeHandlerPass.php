<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QuestionTypeHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $manager = $container->getDefinition('claroline.manager.survey_manager');
        $handlers = $container->findTaggedServiceIds('claroline.survey.question_type_handler');

        foreach ($handlers as $id => $attributes) {
            $manager->addMethodCall(
                'addQuestionTypeHandler',
                array(new Reference($id))
            );
        }
    }
}
