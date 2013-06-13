<?php

namespace ICAP\BlogBundle\Form;

use ICAPLyon1\Bundle\SimpleTagBundle\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('tags', 'zenstruck_ajax_entity', array(
                'mapped'         => false,
                'class'          => 'ICAPLyon1SimpleTagBundle:Tag',
                'property'       => 'name',
                'use_controller' => true,
                'placeholder'    => 'Choose Tags',
                'help'           => 'Try typing a letter such as "o"',
                'multiple'       => true,
                'attr'           => array(
                    'class' => 'span4'
                )
            ))
            ->add('content', 'textarea')
        ;
    }

    public function getName()
    {
        return 'icap_blog_post_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_blog',
            'data_class'      => 'ICAP\BlogBundle\Entity\Post',
            'csrf_protection' => true,
            'intention'       => 'create_post'
        ));
    }
}