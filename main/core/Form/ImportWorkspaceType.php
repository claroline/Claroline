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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportWorkspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array('label' => 'name', 'constraints' => array(new NotBlank()))
        );
        $builder->add(
            'code',
            'text',
            array('label' => 'code', 'constraints' => array(new NotBlank()))
        );
        $builder->add(
            'workspace',
            'file',
            array('label' => 'file', 'constraints' => array(new NotBlank()))
        );
        $builder->add(
            'description',
            isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce'] ?
                'textarea' :
                'tinymce',
            array('required' => false, 'label' => 'description')
        );
        $builder->add(
            'displayable',
            'checkbox',
            array('required' => false, 'label' => 'displayable_in_workspace_list')
        );
        $builder->add(
            'selfRegistration',
            'checkbox',
            array('required' => false, 'label' => 'public_registration')
        );
        $builder->add(
            'registrationValidation',
            'checkbox',
            array('required' => false, 'label' => 'registration_validation')
        );
        $builder->add(
            'selfUnregistration',
            'checkbox',
            array('required' => false, 'label' => 'public_unregistration')
        );
    }

    public function getName()
    {
        return 'workspace_template_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
