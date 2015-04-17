<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('comment', 'tinymce', array());
            // ajouter submit

 $builder->add('save', 'submit', array(
'label' => 'generic.save',
'attr' => array('class' => 'btn btn-default btn-primary'),
));

    }

    public function getName()
    {
        return 'innova_collecticiel_comment_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel'
        ));
    }
}