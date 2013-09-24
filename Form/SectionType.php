<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',      'text')
            ->add('text',   'textarea');
    }

    public function getName()
    {
        return 'icap_wiki_section_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\WikiBundle\Entity\Section'
        ));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'icap_section'
        );
    }
}