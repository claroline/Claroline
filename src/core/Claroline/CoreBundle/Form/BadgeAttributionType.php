<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeAttributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', 'entity', array(
                'class'    => 'ClarolineCoreBundle:User',
                'expanded' => true,
                'multiple' => true,
                'property' => 'username'
            ))
        ;
    }

    public function getName()
    {
        return 'badge_attribution_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class' => 'Claroline\CoreBundle\Entity\Badge\Badge',
                'translation_domain' => 'platform'
            )
        );
    }
}
