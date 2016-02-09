<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Icap\OAuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'client_id',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(),
                        new GreaterThanOrEqual(array('value' => 0))
                    ),
                    'attr' => array('min' => 0),
                    'label' => 'client_id'
                )
            )
            ->add(
                'client_secret',
                'text',
                array(
                    'constraints' => new NotBlank(),
                    'label' => 'client_secret'
                )
            )
            ->add('client_active', 'checkbox', array('label' => 'client_active', 'required' => false));
    }

    public function getName()
    {
        return 'platform_oauth_application_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_oauth',
            'csrf_protection' => true,
            'csrf_field_name' => '_token'
        ));
    }
}
