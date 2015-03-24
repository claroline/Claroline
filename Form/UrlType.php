<?php

namespace HeVinci\UrlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
                'label' => 'name',
                'constraints' => new Assert\NotBlank(),
            )
        );

        $builder->add(
            'url',
            'url',
            array(
                'required' => true,
                'constraints' => new Assert\NotBlank(),
                'label' => 'Url',
            )
        );
    }

    public function getName()
    {
        return 'url_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}