<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeAwardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groups', 'simpleautocomplete', array(
                'entity_reference' => 'group',
                'required'         => false
            ))
            ->add('users', 'simpleautocomplete', array(
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
