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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InternationalizationType extends AbstractType
{
    private $activatedLocales = [];
    private $availableLocales = [];

    public function __construct(array $activatedLocales, array $availableLocales)
    {
        $this->activatedLocales = $activatedLocales;
        foreach ($availableLocales as $available) {
            $this->availableLocales[$available] = $available;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'locales',
            ChoiceType::class, [
                'choices' => $this->availableLocales,
                'label' => 'languages',
                'expanded' => true,
                'multiple' => true,
                'data' => $this->activatedLocales,
            ]
        );
    }

    public function getName()
    {
        return 'i18n_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
        ]);
    }
}
