<?php

namespace Claroline\CoreBundle\Form\Badge;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BadgeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'badge_form_name',
                'constraints' => new Assert\NotBlank(array(
                    'message' => 'badge_translation_need_name'
                ))
            ))
            ->add('description', 'text', array(
                'label' => 'badge_form_description',
                'constraints' => new Assert\NotBlank(array(
                    'message' => 'badge_translation_need_description'
                ))
            ))
            ->add('criteria', 'textarea', array(
                'label' => 'badge_form_criteria',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                ),
                'constraints' => new Assert\NotBlank(array(
                    'message' => 'badge_translation_need_criteria'
                ))
            ))
            ->add('locale', 'hidden');
    }

    public function getName()
    {
        return 'badge_translation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeTranslation',
                    'translation_domain' => 'badge'
                )
            );
    }
}
