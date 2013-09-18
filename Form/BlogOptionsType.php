<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlogOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authorize_comment', 'checkbox', array(
                'required' => false,
            ))
            ->add('authorize_anonymous_comment', 'checkbox', array(
                'required' => false,
            ))
            ->add('post_per_page', 'choice', array(
                'choices'  => array("5" => 5, "10" => 10, "20" => 20),
                'required' => false,
            ))
            ->add('auto_publish_post', 'checkbox', array(
                'required' => false,
            ))
            ->add('auto_publish_comment', 'checkbox', array(
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'icap_blog_options_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'translation_domain' => 'icap_blog',
            'data_class'      => 'Icap\BlogBundle\Entity\BlogOptions',
            'csrf_protection' => true,
            'intention'       => 'configure_blog'
        ));
    }
}
