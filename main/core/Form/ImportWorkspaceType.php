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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportWorkspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            ['label' => 'name', 'constraints' => [new NotBlank()]]
        );
        $builder->add(
            'code',
            TextType::class,
            ['label' => 'code', 'constraints' => [new NotBlank()]]
        );
        $builder->add(
            'workspace',
            FileType::class,
            ['label' => FileType::class, 'mapped' => false, 'required' => false, 'constraints' => [new FileUpload()]]
        );
        $builder->add(
            'fileUrl',
            UrlType::class,
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
            CheckboxType::class,
            ['required' => false, 'label' => 'displayable_in_workspace_list']
        );
        $builder->add(
            'selfRegistration',
            CheckboxType::class,
            ['required' => false, 'label' => 'public_registration']
        );
        $builder->add(
            'registrationValidation',
            CheckboxType::class,
            ['required' => false, 'label' => 'registration_validation']
        );
        $builder->add(
            'selfUnregistration',
            CheckboxType::class,
            ['required' => false, 'label' => 'public_unregistration']
        );
    }

    public function getName()
    {
        return 'workspace_template_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
