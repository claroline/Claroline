<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'hidden', array('data' => 'tmpname'));
        $builder->add(
            'file',
            'file',
            array(
                'required' => true,
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new File()
                )
           )
        );
    }

    public function getName()
    {
        return 'file_form';
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