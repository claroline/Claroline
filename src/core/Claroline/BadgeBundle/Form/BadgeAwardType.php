<?php

namespace Claroline\BadgeBundle\Form;

use Claroline\BadgeBundle\Entity\BadgeTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeAwardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', 'simpleautocomplete', array(
                'entity_reference' => 'group',
                'required'         => false
            ))
            ->add('user', 'simpleautocomplete', array(
                'entity_reference' => 'user',
                'required'         => false,
                'with_vendors'     => false
            ))
        ;
    }

    public function getName()
    {
        return 'badge_award_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
            )
        );
    }
}
