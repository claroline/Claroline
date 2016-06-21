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

class InternationalizationType extends AbstractType
{
    private $activated;
    private $availables;
    private $locales;

    public function __construct($activated, $availables)
    {
        $this->activated = $activated;
        $this->available = array();

        foreach ($availables as $available) {
            $this->available[$available] = $available;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'locales',
            'choice', array(
                'choices' => $this->available,
                'label' => 'languages',
                'expanded' => true,
                'multiple' => true,
                'data' => $this->activated,
            )
        );
    }

    public function getName()
    {
        return 'i18n_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
