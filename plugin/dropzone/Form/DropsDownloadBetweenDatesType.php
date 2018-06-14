<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 06/10/14
 * Time: 11:05.
 */

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropsDownloadBetweenDatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = [
            'required' => false,
            'read_only' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'format' => $options['date_format'],
            'mapped' => false,
        ];

        $builder
            ->add('drop_period_begin_date', DatePickerType::class, $defaultDateTimeOptions)
            ->add('drop_period_end_date', DatePickerType::class, $defaultDateTimeOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'language' => 'en',
                'translation_domain' => 'icap_dropzone',
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }
}
