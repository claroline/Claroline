<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WidgetDisplayType extends AbstractType
{
    private $isLocked;
    
    public function __construct($isLocked = false)
    {
        $this->isLocked = $isLocked;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isLocked) {
            $builder->add('name', 'text', array('read_only' => true));
        }  else {
            $builder->add('name', 'text');
        }
        
    }

    public function getName()
    {
        return 'widget_display_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
