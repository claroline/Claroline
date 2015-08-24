<?php

namespace FormaLibre\ReservationBundle\Form;

use Symfony\Component\Form\AbstractType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @DI\Service("formalibre.form.reservation")
 */
class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start', 'datetime', array(
            'label' => 'agenda.form.start_date',
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'input' => 'timestamp',
            'constraints' => [new NotBlank()]
        ));

        $builder->add('end', 'datetime', array(
            'label' => 'agenda.form.end_date',
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'input' => 'timestamp',
            'constraints' => [new NotBlank()]
        ));

        $builder->add('duration', 'text', array(
            'label' => 'agenda.form.duration',
            'attr' => array(
                'placeholder' => 'hh:mm'
            )
        ));

        $builder->add('resource', 'entity', array(
            'label' => 'agenda.form.resource',
            'class' => 'FormaLibre\ReservationBundle\Entity\Resource',
            'property' => 'name',
            'group_by' => 'resource_type.name',
            'empty_value' => 'agenda.form.select_resource_pls'
        ));
    }

    public function getName()
    {
        return 'reservation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'FormaLibre\ReservationBundle\Entity\Reservation',
                'translation_domain' => 'reservation'
            )
        );
    }
}