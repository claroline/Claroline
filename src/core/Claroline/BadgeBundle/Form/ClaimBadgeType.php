<?php

namespace Claroline\BadgeBundle\Form;

use Claroline\BadgeBundle\Entity\BadgeTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClaimBadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('badge', 'simpleautocomplete', array(
                'entity_reference' => 'badge',
                'required'         => false,
                'format'           => 'jsonp',
                'mapped'           => false
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
                'data_class'         => 'Claroline\BadgeBundle\Entity\BadgeClaim',
                'translation_domain' => 'platform'
            )
        );
    }
}
