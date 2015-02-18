<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ip', 'hidden')
                ->add('mark', 'hidden')
                ->add('nb_tries', 'hidden')
            //->add('response')
            //->add('paper')
            //->add('interaction')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Response',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_responsetype';
    }
}