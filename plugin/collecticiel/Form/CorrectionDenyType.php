<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionDenyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('correctionDeniedComment', 'tinymce', array(
            'label_attr' => array(
                'style' => 'display: none;',
            ),
            'required' => true,
        ));
    }

    public function getName()
    {
        return 'innova_collecticiel_correction_deny_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
