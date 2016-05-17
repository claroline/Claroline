<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DropzoneAppreciationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gradingScales', 'collection',
                array(
                    'type' => new GradingScaleType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'mapped' => true,
                    'by_reference' => false,
                    )
                 )
            ->add('gradingCriterias', 'collection',
                array(
                    'type' => new GradingCriteriaType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'mapped' => true,
                    'by_reference' => false,
                    )
                 )
            ;
    }

    public function getName()
    {
        return 'innova_collecticiel_appreciation_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'language' => 'fr',
                'translation_domain' => 'innova_collecticiel',
                'data_class' => 'Innova\CollecticielBundle\Entity\Dropzone',
                'cascade_validation' => true,
                'date_format' => DateType::HTML5_FORMAT,
            )
        );
    }
}
