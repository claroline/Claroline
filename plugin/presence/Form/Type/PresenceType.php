<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PresenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add(
                 'userStudent',
                 'entity',
                 [
                     'required' => false,
                     'disabled' => true,
                     'read_only' => true,
                     'class' => 'Claroline\CoreBundle\Entity\User',
                     'property' => 'UserName',
                 ]
             )
             ->add(
                 'Status',
                 'entity',
                 [
                     'multiple' => false,
                     'expanded' => true,
                     'label' => 'Status:',
                     'class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'property' => 'statusName',

                 ]

             );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'FormaLibre\PresenceBundle\Entity\Presence',
            ]);
    }

    public function getName()
    {
        return 'Releve';
    }
}
