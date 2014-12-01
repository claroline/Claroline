<?php

namespace Icap\BlogBundle\Form;

use Icap\BlogBundle\Form\DataTransformer\IntToBlogTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Zenstruck\Bundle\FormBundle\Form\DataTransformer\AjaxEntityTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_blog.form.widget_list")
 * @DI\FormType(alias = "widgetList")
 */
class WidgetListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('widgetListBlogs', 'collection', array(
            'type'          => 'blog_widget_blog_form',
            'by_reference'  => false,
            'prototype'     => true,
            'allow_add'     => true,
            'allow_delete'  => true
        ));
    }

    public function getName()
    {
        return 'blog_widget_list_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\BlogBundle\Entity\WidgetBlogList',
                'translation_domain' => 'icap_blog'
            )
        );
    }
}