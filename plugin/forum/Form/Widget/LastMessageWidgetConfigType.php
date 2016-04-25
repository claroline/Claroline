<?php

namespace Claroline\ForumBundle\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LastMessageWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('display_my_last_messages', 'checkbox', array(
            'theme_options' => array(
                'control_width' => 'col-md-6',
                'label_width' => 'col-md-6',
            ),
        ));
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
