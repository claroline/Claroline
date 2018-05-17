<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Log;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogDesktopWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $workspaces = $options['workspaces'];

        if (is_array($workspaces)) {
            foreach ($workspaces as $workspace) {
                $builder->add(
                    $workspace->getId(),
                    CheckboxType::class,
                    ['required' => false, 'label' => $workspace->getName()]
                );
            }
        }
        $builder->add(
            'amount',
            ChoiceType::class,
            [
                'choices' => [
                    '1' => '1',
                    '5' => '5',
                    '10' => '10',
                    '15' => '15',
                    '20' => '20',
                ],
                'required' => true,
            ]
        );
    }

    public function getName()
    {
        return 'log_hidden_workspace_widget_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'workspaces' => [],
                'translation_domain' => 'log',
            ]
        );
    }
}
