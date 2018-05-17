<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DropzoneCriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goBack', HiddenType::class, array(
                'mapped' => false,
            ))

// Ajout du nom du critère
            ->add('name', TextType::class, array(
                'constraints' => new NotBlank(),
                'required' => true,
            ))

            ->add('recalculateGrades', HiddenType::class, array('mapped' => false));
    }

    public function getName()
    {
        return 'innova_collecticiel_criteria_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
