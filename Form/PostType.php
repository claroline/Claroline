<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'theme_options' => array('control_width' => 'col-md-12')
                )
            )
            ->add('content', 'textarea', array(
                    'attr' => array(
                        'class'      => 'form-control tinymce',
                        'data-theme' => 'medium',
                        'style'      => 'height: 300px;'
                    ),
                    'theme_options' => array('control_width' => 'col-md-12')
                )
            )
            ->add('tags', 'tags')
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
            'data_class'      => 'Icap\BlogBundle\Entity\Post',
            'csrf_protection' => true,
            'intention'       => 'create_post'
        ));
    }
}
