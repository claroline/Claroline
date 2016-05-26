<?php

namespace Claroline\ForumBundle\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LastMessageWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'forum',
            'resourcePicker',
            array(
                'label' => 'forum',
                'attr' => array(
                    'data-is-picker-multi-select-allowed' => 0,
                    'data-is-directory-selection-allowed' => 0,
                    'data-type-white-list' => 'claroline_forum',
                ),
                'display_browse_button' => false,
                'display_download_button' => false,
            )
        );
        $builder->add(
            'displayMyLastMessages',
            'checkbox'
        );
    }

    public function getName()
    {
        return 'claroline_forum_last_message_widget_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig',
                'translation_domain' => 'forum',
            )
        );
    }
}
