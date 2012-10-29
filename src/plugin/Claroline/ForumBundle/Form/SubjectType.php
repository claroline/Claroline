<?php

namespace Claroline\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Claroline\ForumBundle\Entity\Message;

class SubjectType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text')
            ->add('name', 'hidden', array('data' => 'tmp'))
            ->add('messages', new MessageType());

    }

    public function getName()
    {
        return 'subject_forum_form';
    }



}