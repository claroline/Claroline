<?php

namespace Icap\BadgeBundle\Form\Type\Portfolio;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class BadgesBadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('img', 'text', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('badge', 'entity', array(
                'class' => 'IcapBadgeBundle:Badge',
                'property' => 'name',
                'required' => false,
            ));
    }

    public function getName()
    {
        return 'icap_badge_portfolio_widget_form_badges_badge';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\Portfolio\BadgesWidgetBadge',
                'translation_domain' => 'icap_badge',
                'csrf_protection' => false,
            )
        );
    }
}
