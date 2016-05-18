<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogBannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('banner_activate', 'checkbox', array(
                'required' => false,
            ))
            ->add('banner_background_color', 'text', array(
                'theme_options' => array('label_width' => ''),
            ))
            ->add('banner_height', 'text', array(
                'theme_options' => array('label_width' => ''),
                'attr' => array(
                    'class' => 'input-sm',
                    'data-min' => 100,
                ),
            ))
            ->add('file', 'file', array(
                'label' => 'icap_blog_banner_form_banner_background_image',
                'theme_options' => array('label_width' => ''),
                'required' => false,
            ))
            ->add('banner_background_image_position', 'text', array(
                'theme_options' => array('label_width' => ''),
                'required' => false,
            ))
            ->add('banner_background_image_repeat', 'text', array(
                'theme_options' => array('label_width' => ''),
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'icap_blog_banner_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
           'translation_domain' => 'icap_blog',
            'data_class' => 'Icap\BlogBundle\Entity\BlogOptions',
            'csrf_protection' => true,
            'intention' => 'configure_banner_blog',
            'no_captcha' => true,
        ));
    }
}
