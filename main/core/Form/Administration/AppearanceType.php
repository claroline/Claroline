<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppearanceType extends AbstractType
{
    private $themes;
    private $iconSets;
    private $lockedParams;

    public function __construct(array $themes, array $iconSets, array $lockedParams = [])
    {
        $this->themes = $themes;
        $this->iconSets = $iconSets;
        $this->lockedParams = $lockedParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name_active',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'show_name_in_top_bar',
                    'disabled' => isset($this->lockedParams['nameActive']),
                ]
            )
            ->add(
                'footer',
                'text',
                [
                    'required' => false,
                    'disabled' => isset($this->lockedParams['footer']),
                    'label' => 'footer',
                ]
            )
            ->add(
                'theme',
                'choice',
                [
                    'choices' => $this->themes,
                    'disabled' => isset($this->lockedParams['theme']),
                    'label' => 'theme',
                ]
            )
            ->add(
                'resource_icon_set',
                'choice',
                [
                    'choices' => $this->iconSets,
                    'disabled' => isset($this->lockedParams['resource_icon_set']),
                    'label' => 'resource_icon_set',
                ]
            );
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
