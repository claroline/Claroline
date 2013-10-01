<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        $isRootSection = $options['isRootSection'];
        $sectionId = $builder->getData()->getId();

        if (!$isRootSection) {
            $forbidenIds = array($sectionId);
            foreach ($options['sections'] as $section){
                if (!in_array($section->getParent()->getId(), $forbidenIds)) {
                    $choices[$section->getId()] = $section->getTitle();
                }
                else {
                    array_push($forbidenIds, $section->getId());
                }
            }

            $builder
            ->add('title', 'text')
            ->add('text', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                    )
                )
            )
            ->add('visible', 'checkbox', array(
                'required' => false    
                )
            )
            ->add('position', 'choice', array(
                'mapped' => false,
                'choices' => $choices,
                'data' => $sectionId
                )
            )
            ->add('brother', 'checkbox', array(
                'mapped' => false,
                'required' => false
                )
            );   
        }
        else {
           $builder
            ->add('text', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                    )
                )
            ); 
        }        
    }

    public function getName()
    {
        return 'icap_wiki_edit_section_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Section',
            'sections' => array(),
            'isRootSection' => false
        ));
    }
}