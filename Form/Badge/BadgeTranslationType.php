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

class BadgeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'badge_form_name'
            ))
            ->add('description', 'text', array(
                'label' => 'badge_form_description'
            ))
            ->add('criteria', 'tinymce', array(
                'label' => 'badge_form_criteria'
            ))
            ->add('locale', 'hidden');
    }

    public function getName()
    {
        return 'badge_translation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeTranslation',
                    'translation_domain' => 'badge'
                )
            );
    }
}
