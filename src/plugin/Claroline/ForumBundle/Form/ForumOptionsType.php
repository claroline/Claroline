<?php

namespace Claroline\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ForumOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('messages');
        $builder->add('subjects');
    }

    public function getName()
    {
        return 'forum_options_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'forum'
        );
    }
}