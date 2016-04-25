<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogInfosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('infos', 'tinymce');
    }

    public function getName()
    {
        return 'icap_blog_infos_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_blog',
            'data_class' => 'Icap\BlogBundle\Entity\Blog',
            'csrf_protection' => true,
            'intention' => 'edit_blog_infos',
        ));
    }
}
