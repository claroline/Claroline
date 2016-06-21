<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WikiOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', 'choice', array(
                'choices' => array(
                    '0' => 'normal',
                    '1' => 'moderate',
                    '2' => 'read_only',
                ),
                'multiple' => false,
                'expanded' => true,
            ));
    }

    public function getName()
    {
        return 'icap_wiki_options_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Wiki',
        ));
    }
}
