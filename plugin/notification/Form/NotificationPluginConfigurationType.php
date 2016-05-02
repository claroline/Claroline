<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/14/15
 */

namespace Icap\NotificationBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NotificationPluginConfigurationType.
 *
 * @DI\FormType
 */
class NotificationPluginConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dropdownItems', 'integer',
                array(
                    'label' => 'dropdown_items',
                    'theme_options' => array('control_width' => 'col-md-2'),
                )
            )
            ->add('maxPerPage', 'integer',
                array(
                    'label' => 'max_per_page',
                    'theme_options' => array('control_width' => 'col-md-2'),
                )
            )
            ->add('purgeEnabled', 'checkbox',
                array(
                    'required' => false,
                    'label' => 'purge_enabled',
                    'theme_options' => array('control_width' => 'col-md-2'),
                )
            )
            ->add('purgeAfterDays', 'integer',
                array(
                    'label' => 'purge_after_days',
                    'theme_options' => array('control_width' => 'col-md-2'),
                )
            );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'icap_notification_type_pluginConfiguration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'notification',
                'data_class' => 'Icap\NotificationBundle\Entity\NotificationPluginConfiguration',
                'csrf_protection' => true,
            )
        );
    }
}
