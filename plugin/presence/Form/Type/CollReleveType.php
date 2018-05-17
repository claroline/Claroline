<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollReleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('releves', 'collection', array('type' => new PresenceType(),
                                                         'allow_add' => true,
                                                         'allow_delete' => true, ))
                    ->add('Valider', 'submit', array(
                        'label' => 'Valider les présences',
                    ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PresenceBundle\Entity\Releves',
            ));
    }

    public function getName()
    {
        return 'CollReleve';
    }
}
