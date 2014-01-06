<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Badge;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeAwardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'group',
                'simpleautocomplete',
                array(
                    'entity_reference' => 'group',
                    'required'         => false
                )
            )
            ->add(
                'user',
                'simpleautocomplete',
                array(
                    'entity_reference' => 'user',
                    'required'         => false,
                    'with_vendors'     => false
                )
            );
    }

    public function getName()
    {
        return 'badge_award_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'badge'));
    }
}
