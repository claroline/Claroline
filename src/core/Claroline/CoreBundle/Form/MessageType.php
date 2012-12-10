<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageType extends AbstractType
{
    public function __construct($username = '', $object = '', $isFast = false)
    {
        $this->username = $username;
        $this->isFast = $isFast;
        $this->object = $object;

        if ($isFast) {
            if($username == '' || $username == null){
                throw new \Exception('username required');
            }
            if($object == '' || $object == null){
                throw new \Exception('object required');
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->isFast){
        $builder
            ->add('to', 'text', array('data' => $this->username, 'required' => true))
            ->add('object', 'text', array('data' => $this->object, 'required' => true))
            ->add('content', 'textarea', array('required' => true));
        } else {
        $builder
            ->add('to', 'hidden', array('data' => $this->username, 'required' => true))
            ->add('object', 'hidden', array('data' => $this->object, 'required' => true))
            ->add('content', 'textarea', array('required' => true));
        }
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