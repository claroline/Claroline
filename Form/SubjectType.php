<?php

namespace Claroline\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubjectType extends AbstractType
{
    private $msgVisible;

    public function __construct($msgVisible)
    {
        $this->msgVisible = $msgVisible;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        
        if ($this->msgVisible === 'true') {
            $builder->add('message', new MessageType());
        } else {
            $builder->add('message', new MessageType(), array('disabled' => true));
        }
    }

    public function getName()
    {
        return 'forum_subject_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forum'
            )
        );
    }
}