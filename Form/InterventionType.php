<?php

namespace FormaLibre\SupportBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InterventionType extends AbstractType
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'intervenant',
            'text',
            array(
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'label' => 'intervenant',
                'data' => $this->user->getFirstName() . ' ' . $this->user->getLastName()
            )
        );
        $builder->add(
            'startDate',
            'datetimepicker',
            array(
                'required' => true,
                'read_only' => false,
                'component' => true,
                'autoclose' => true,
                'language' => 'fr',
//                'date_format' => 'dd-MM-yyyy',
//                'format' => 'dd-MM-yyyy H:m',
                'date_format' => DateType::HTML5_FORMAT,
                'translation_domain' => 'platform',
                'label' => 'start_date'
            )
        );
        $builder->add(
            'endDate',
            'datetimepicker',
            array(
                'required' => false,
                'read_only' => false,
                'component' => true,
                'autoclose' => true,
                'language' => 'fr',
//                'date_format' => 'dd-MM-yyyy',
//                'format' => 'dd-MM-yyyy H:m',
                'date_format' => DateType::HTML5_FORMAT,
                'translation_domain' => 'platform',
                'label' => 'end_date'
            )
        );
        $builder->add(
            'duration',
            'integer',
            array(
                'label' => 'duration_in_minute',
                'required' => false,
            )
        );
        $builder->add(
            'computeTimeMode',
            'choice',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'compute_time_mode',
                'choices' => array(
                    0 => 'compute_time_from_end_date',
                    1 => 'compute_time_from_duration'
                )
            )
        );
        $builder->add(
            'status',
            'entity',
            array(
                'label' => 'status',
                'class' => 'FormaLibreSupportBundle:Status',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.order', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            )
        );
        $builder->add(
            'externalComment',
            'tinymce',
            array(
                'required' => true,
                'label' => 'client_message'
            )
        );
        $builder->add(
            'internalComment',
            'tinymce',
            array(
                'required' => true,
                'label' => 'internal_message'
            )
        );
    }

    public function getName()
    {
        return 'intervention_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
