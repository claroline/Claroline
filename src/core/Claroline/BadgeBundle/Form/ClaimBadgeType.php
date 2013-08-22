<?php

namespace Claroline\BadgeBundle\Form;

use Symfony\Component\Form\AbstractType;
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
        return 'badge_claim_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class'         => 'Claroline\BadgeBundle\Entity\BadgeClaim',
                'translation_domain' => 'badge'
            )
        );
    }
}
