<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneNotationCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gradingNotations', 'collection',
                [
                    'type' => new GradingNotationType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'mapped' => true,
                    'by_reference' => false,
                ]
            )
        ;
    }

    public function getName()
    {
        return 'innova_collecticiel_notation_collection_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'language' => 'fr',
                'translation_domain' => 'innova_collecticiel',
                'data_class' => 'Innova\CollecticielBundle\Entity\Dropzone',
                'cascade_validation' => true,
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }
}
