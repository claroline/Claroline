<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Validator\Constraints\WorkspaceUniqueCode;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('required' => true));
        $builder->add(
            'code',
            'text',
            array(
                'required' => true,
                'constraints' => array(new WorkspaceUniqueCode())
                )
        );
        if (isset($options['theme_options']['tinymce']) and !$options['theme_options']['tinymce']) {
            $builder->add(
                'description',
                'textarea', 
                array('required' => false)
            );
        } else {
            $builder->add('description', 'tinymce', array('required' => false));
        }
        $builder->add(
            'type',
            'choice',
            array(
                'choices' => array(
                    'simple' => 'Simple',
                    'aggregator' => 'Aggregator',
                ),
                'multiple' => false,
                'required' => true
            )
        );

        $builder->add(
            'template',
            'entity',
            array(
                'class' => 'ClarolineCoreBundle:Workspace\Template',
                'property' => 'name'
            )
        );
        $builder->add('displayable', 'checkbox', array('required' => false));
        $builder->add('selfRegistration', 'checkbox', array('required' => false));
        $builder->add(
            'selfUnregistration',
            'checkbox',
            array(
                'required' => false,
                'attr' => array('checked' => 'checked')
            )
        );
    }

    public function getName()
    {
        return 'workspace_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}
