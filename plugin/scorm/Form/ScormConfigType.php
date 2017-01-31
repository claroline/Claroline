<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Form;

use Claroline\ScormBundle\Entity\ScormResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScormConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'hideTopBar',
            'checkbox',
            [
                'label' => 'hide_top_bar',
                'translation_domain' => 'scorm',
            ]
        );
        $builder->add(
            'exitMode',
            'choice',
            [
                'choices' => [
                    'workspace_opening' => ScormResource::WORKSPACE_OPEN,
                    'desktop' => ScormResource::DESKTOP_OPEN,
                ],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'exit_destination',
                'translation_domain' => 'scorm',
                'choice_translation_domain' => true,
            ]
        );
    }

    public function getName()
    {
        return 'scorm_config_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'scorm']);
    }
}
