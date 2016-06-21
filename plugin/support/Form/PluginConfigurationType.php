<?php

namespace FormaLibre\SupportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PluginConfigurationType extends AbstractType
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'withCredits',
            'checkbox',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'with_credits',
                'data' => isset($this->config['with_credits']) ? $this->config['with_credits'] : false,
            )
        );
    }

    public function getName()
    {
        return 'plugin_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
