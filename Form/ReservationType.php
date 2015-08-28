<?php

namespace FormaLibre\ReservationBundle\Form;

use Doctrine\ORM\EntityRepository;
use FormaLibre\ReservationBundle\Controller\ReservationController;
use FormaLibre\ReservationBundle\Manager\ReservationManager;
use FormaLibre\ReservationBundle\Validator\Constraints\Reservation;
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
    private $editMode = false;
    private $reservationManager;

    /**
     * @DI\InjectParams({
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager")
     * })
     */
    public function __construct(ReservationManager $reservationManager)
    {
        $this->reservationManager = $reservationManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start', 'text', array(
            'label' => 'agenda.form.start_date'
        ));

        $builder->add('end', 'text', array(
            'label' => 'agenda.form.end_date'
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
            'query_builder' => function(EntityRepository $er) {
                $resources =  $er->createQueryBuilder('r');

                $mask = $this->editMode ? ReservationController::EDIT : ReservationController::ADMIN;
                foreach ($resources as $key => $resource) {
                    if (!$this->reservationManager->hasAccess($resource, $mask)) {
                        unset($resources[$key]);
                    }
                }

                return $resources;
            },
            'property' => 'name',
            'group_by' => 'resource_type.name',
            'empty_value' => 'agenda.form.select_resource_pls'
        ));
    }

    public function setEditMode()
    {
        $this->editMode = true;
    }

    public function getName()
    {
        return 'reservation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if (!$this->editMode) {
            $resolver->setDefaults(
                array(
                    'class' => 'FormaLibre\ReservationBundle\Entity\Reservation',
                    'translation_domain' => 'reservation',
                    'constraints' => new Reservation()
                )
            );
        } else {
            $resolver->setDefaults(
                array(
                    'class' => 'FormaLibre\ReservationBundle\Entity\Reservation',
                    'translation_domain' => 'reservation'
                )
            );
        }
    }
}