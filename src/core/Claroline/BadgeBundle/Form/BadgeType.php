<?php

namespace Claroline\BadgeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('frTranslation', new BadgeTranslationType())
            ->add('enTranslation', new BadgeTranslationType())
            ->add('version', 'integer')
            ->add('file', 'file', array(
                    'label' => 'badge_form_image'
                ))
            ->add('expired_at', 'datepicker', array(
                  'read_only' => true,
                  'component' => true,
                  'autoclose' => true,
                  'language'  => $options['language'],
                  'format'    => $options['date_format']
            ))
        ;
    }

    public function getName()
    {
        return 'badge_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class'         => 'Claroline\BadgeBundle\Entity\Badge',
                'translation_domain' => 'badge',
                'language'           => 'en',
                'date_format'        => DateType::HTML5_FORMAT
            )
        );
    }
}
