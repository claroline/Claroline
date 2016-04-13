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
    private $lockedParams;

    public function __construct(array $themes, array $lockedParams = array())
    {
        $this->themes = $themes;
        $this->lockedParams = $lockedParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name_active',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'show_name_in_top_bar',
                    'disabled' => isset($this->lockedParams['nameActive']),
                )
            )
            ->add(
                'footer',
                'text',
                array(
                    'required' => false,
                    'disabled' => isset($this->lockedParams['footer']),
                    'label' => 'footer',
                )
            )
            ->add(
                'theme',
                'choice',
                array(
                    'choices' => $this->themes,
                    'disabled' => isset($this->lockedParams['theme']),
                    'label' => 'theme',
                )
            );
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
