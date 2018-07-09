<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'badge_form_name', 'attr' => ['maxlength' => 128]])
            ->add('description', TextType::class, ['label' => 'badge_form_description', 'attr' => ['maxlength' => 128]])
            ->add('criteria', TinymceType::class, ['label' => 'badge_form_criteria'])
            ->add('locale', HiddenType::class);
    }

    public function getName()
    {
        return 'badge_translation_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeTranslation',
                'translation_domain' => 'icap_badge',
            ]
        );
    }
}
