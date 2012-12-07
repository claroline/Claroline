<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageType extends AbstractType
{
    public function __construct($username = '')
    {
        $this->username = $username;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('to', 'text', array('data' => $this->username, 'required' => true))
        ->add('object', 'text', array('required' => true))
        ->add('content', 'textarea', array('required' => true));
    }

    public function getName()
    {
        return 'message_form';
    }

    public function getDefaultOptions(array $options)
    {
       return array(
           'translation_domain' => 'platform'
       );
    }
}