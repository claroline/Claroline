<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DropzoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',
        array(
            'constraints' => new NotBlank(),
            'required' => true,
            'attr' => array('autofocus' => true),
             )
        );
    }

    public function getName()
    {
        return 'innova_collecticiel_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
