<?php

namespace FormaLibre\SupportBundle\Form;

use FormaLibre\SupportBundle\Entity\Ticket;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketTypeChangeType extends AbstractType
{
    private $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = $this->ticket->getType();

        $builder->add(
            'type',
            'entity',
            array(
                'label' => 'type',
                'class' => 'FormaLibreSupportBundle:Type',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) use ($type) {
                    return $er->createQueryBuilder('t')
                        ->where('t.id != :typeId')
                        ->setParameter('typeId', $type->getId())
                        ->orderBy('t.name', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            )
        );
    }

    public function getName()
    {
        return 'ticket_type_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
