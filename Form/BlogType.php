<?php

namespace ICAP\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
    }

    public function getName()
    {
        return 'icap_blog_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'translation_domain' => 'icap_blog',
            'data_class'      => 'ICAP\BlogBundle\Entity\Blog',
            'csrf_protection' => true,
            'intention'       => 'create_blog'
        ));
    }
}
