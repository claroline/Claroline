<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleTextType extends AbstractType
{
    private $formName;

    public function __construct($formName = null)
    {
        $this->formName = $formName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'tinymce');
    }

    public function getName()
    {
        return $this->formName;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
