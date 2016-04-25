<?php

namespace Icap\BadgeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'badge_form_name', 'attr' => array('maxlength' => 128)))
            ->add('description', 'text', array('label' => 'badge_form_description', 'attr' => array('maxlength' => 128)))
            ->add('criteria', 'tinymce', array('label' => 'badge_form_criteria'))
            ->add('locale', 'hidden');
    }

    public function getName()
    {
        return 'badge_translation_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeTranslation',
                'translation_domain' => 'icap_badge',
            )
        );
    }
}
