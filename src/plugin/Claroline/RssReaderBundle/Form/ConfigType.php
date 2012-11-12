<?php

namespace Claroline\RssReaderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'text', array('required' => false));
    }

    public function getName()
    {
        return 'rss_form';
    }
}