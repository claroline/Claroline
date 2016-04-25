<?php

namespace FormaLibre\ReservationBundle\Form;

use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Controller\ReservationController;
use FormaLibre\ReservationBundle\Manager\ReservationManager;
use FormaLibre\ReservationBundle\Validator\Constraints\Reservation;
use FormaLibre\ReservationBundle\Validator\Constraints\ReservationModify;
use Symfony\Component\Form\AbstractType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("formalibre.form.reservation")
 */
class ReservationType extends AbstractType
{
    private $editMode = false;
    private $reservationManager;
    private $em;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "tokenStorage"       = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(ReservationManager $reservationManager, EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->reservationManager = $reservationManager;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start', 'text', array(
            'label' => 'agenda.form.start_date',
        ));

        $builder->add('end', 'text', array(
            'label' => 'agenda.form.end_date',
        ));

        $builder->add('duration', 'text', array(
            'label' => 'agenda.form.duration',
            'attr' => array(
                'placeholder' => 'hh:mm',
            ),
        ));

        $builder->add('comment', 'textarea', [
            'label' => 'agenda.form.comment',
            'required' => false,
            'max_length' => 255,
            'attr' => [
                'placeholder' => 'agenda.form.max_255_characters',
            ],
        ]);

        $builder->add('resource', 'entity', array(
            'label' => 'agenda.form.resource',
            'class' => 'FormaLibre\ReservationBundle\Entity\Resource',
            'choices' => $this->getResourceByMask(),
            'property' => 'name',
            'group_by' => 'resource_type.name',
            'empty_value' => 'agenda.form.select_resource_pls',
        ));
    }

    public function getResourceByMask()
    {
        $resources = $this->em->getRepository('FormaLibreReservationBundle:Resource')->findAll();
        $mask = ReservationController::BOOK;

        foreach ($resources as $key => $resource) {
            if (!$this->reservationManager->hasAccess($this->tokenStorage->getToken()->getUser(), $resource, $mask)) {
                unset($resources[$key]);
            }
        }

        return $resources;
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
        if ($this->editMode) {
            $resolver->setDefaults(
                array(
                    'class' => 'FormaLibre\ReservationBundle\Entity\Reservation',
                    'translation_domain' => 'reservation',
                    'constraints' => new ReservationModify(),
                )
            );
        } else {
            $resolver->setDefaults(
                array(
                    'class' => 'FormaLibre\ReservationBundle\Entity\Reservation',
                    'translation_domain' => 'reservation',
                    'constraints' => new Reservation(),
                )
            );
        }
    }
}
