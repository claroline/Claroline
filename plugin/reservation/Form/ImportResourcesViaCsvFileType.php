<?php

namespace FormaLibre\ReservationBundle\Form;

use FormaLibre\ReservationBundle\Validator\Constraints\CsvResource;
use Symfony\Component\Form\AbstractType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @DI\Service("formalibre.form.reservation_import_resources_form")
 */
class ImportResourcesViaCsvFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', [
            'label' => 'import.file',
            'constraints' => array(
                new NotBlank(),
                new File(),
                new CsvResource(),
            ),
            'mapped' => false,
        ]);
    }

    public function getName()
    {
        return 'import_resources_via_csv_file_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'reservation',
        ]);
    }
}
