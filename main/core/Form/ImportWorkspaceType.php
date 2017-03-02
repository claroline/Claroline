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

use Claroline\CoreBundle\Validator\Constraints\FileUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportWorkspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            ['label' => 'name', 'constraints' => [new NotBlank()]]
        );
        $builder->add(
            'code',
            'text',
            ['label' => 'code', 'constraints' => [new NotBlank()]]
        );
        $builder->add(
            'workspace',
            'file',
            ['label' => 'file', 'mapped' => false, 'required' => false, 'constraints' => [new FileUpload()]]
        );
        $builder->add(
            'fileUrl',
            'url',
            ['label' => 'URL', 'mapped' => false, 'required' => false]
        );
        $builder->add(
            'description',
            isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce'] ?
                'textarea' :
                'tinymce',
            ['required' => false, 'label' => 'description']
        );
        $builder->add(
            'displayable',
            'checkbox',
            ['required' => false, 'label' => 'displayable_in_workspace_list']
        );
        $builder->add(
            'selfRegistration',
            'checkbox',
            ['required' => false, 'label' => 'public_registration']
        );
        $builder->add(
            'registrationValidation',
            'checkbox',
            ['required' => false, 'label' => 'registration_validation']
        );
        $builder->add(
            'selfUnregistration',
            'checkbox',
            ['required' => false, 'label' => 'public_unregistration']
        );
    }

    public function getName()
    {
        return 'workspace_template_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
