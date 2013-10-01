<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeleteSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['hasChildren'] == true) {
            $builder ->add('children', 'checkbox', array(
                'required' => false,
                'mapped' => false
            ));
        }
        else {
            $builder ->add('children', 'hidden', array(
                'required' => false,
                'mapped' => false
            ));
        }            
    }

    public function getName()
    {
        return 'icap_wiki_delete_section_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Section',
            'hasChildren' => true
        ));
    }
}