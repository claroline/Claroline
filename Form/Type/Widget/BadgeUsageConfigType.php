<?php

namespace Icap\BadgeBundle\Form\Badge\Type\Widget;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType(alias="badge_usage_widget_config")
 */
class BadgeUsageConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number_last_awarded_badge', 'integer', array(
                'theme_options' => array('control_width' => 'col-md-2')
            ))
            ->add('number_most_awarded_badge', 'integer', array(
                'theme_options' => array('control_width' => 'col-md-2')
            ));
    }

    public function getName()
    {
        return 'badge_usage_widget_config';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\Badge\Widget\BadgeUsageConfig',
                'translation_domain' => 'badge',
                'language'           => 'en'
            )
        );
    }
}
