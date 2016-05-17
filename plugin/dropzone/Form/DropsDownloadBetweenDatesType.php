<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 06/10/14
 * Time: 11:05.
 */

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropsDownloadBetweenDatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = array(
            'required' => false,
            'read_only' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'format' => $options['date_format'],
            'mapped' => false,
        );

        $builder
            ->add('drop_period_begin_date', 'datepicker', $defaultDateTimeOptions)
            ->add('drop_period_end_date', 'datepicker', $defaultDateTimeOptions);
    }

    public function getName()
    {
        return 'icap_dropzone_date_download_between_date_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'language' => 'en',
                'translation_domain' => 'icap_dropzone',
                'date_format' => DateType::HTML5_FORMAT,
            )
        );
    }
}
