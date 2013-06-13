<?php

namespace Claroline\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'content', 
            'textarea',
            array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'medium'
                    )
                )
        );
    }

    public function getName()
    {
        return 'forum_message_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'forum'
        );
    }
}