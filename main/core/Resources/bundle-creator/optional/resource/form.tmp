<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class [[Resource_Type]]Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('constraints' => new NotBlank(), 'label' => 'name'));
        $builder->add(
            'published',
            'checkbox',
            array(
                'label' => 'publish',
                'required' => true,
                'mapped' => false,
                'attr' => array('checked' => 'checked')
           )
        );
    }

    public function getName()
    {
        return '[[resource_type]]_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => '[[resource_type]]'));
    }
}
