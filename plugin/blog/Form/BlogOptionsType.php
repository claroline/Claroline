<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authorize_comment', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('authorize_anonymous_comment', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('auto_publish_post', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('auto_publish_comment', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('display_title', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('display_post_view_counter', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('post_per_page', ChoiceType::class, array(
                'choices' => array('5' => 5, '10' => 10, '20' => 20),
                'required' => false,
                'attr' => array('control_width' => 'col-md-2'),
            ))
            ->add('tag_cloud', ChoiceType::class, array(
                'choices' => array('0' => 'classic', '1' => '3D', '2' => 'advanced'),
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
            ))
            ->add('tag_top_mode', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('max_tag', 'integer')
            ->add('listWidgetBlog', 'listWidgetBlog', array(
                'label' => 'Trier les rubriques',
            ))
        ;
    }

    public function getName()
    {
        return 'icap_blog_options_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
           'translation_domain' => 'icap_blog',
            'data_class' => 'Icap\BlogBundle\Entity\BlogOptions',
            'csrf_protection' => true,
            'intention' => 'configure_blog',
        ));
    }
}
