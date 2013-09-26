<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'file',
                array(
                    'required' => false,
                    'mapped' => false,
                    'constraints' => array(
                        new Image(array(
                            'minWidth' => 50,
                            'maxWidth' => 800,
                            'minHeight' => 50,
                            'maxHeight' => 800,
                            )
                        )
                    )
               )
            );
    }

    public function getName()
    {
        return 'picture_form';
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