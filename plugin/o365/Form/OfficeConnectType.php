<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\OfficeConnectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class OfficeConnectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'office_client_id',
                'text',
                array(
                    'constraints' => new NotBlank(),
                    'attr' => array('min' => 0),
                    'label' => 'office_client_id',
                )
            )
            ->add(
                'office_password',
                'text',
                array(
                    'constraints' => new NotBlank(),
                    'label' => 'password',
                )
            )
            ->add(
                'office_app_tenant_domain_name',
                'text',
                array(
                    'constraints' => new NotBlank(),
                    'label' => 'app_tenant_domain_name',
                )
            )
            ->add('office_client_active', 'checkbox', array('label' => 'active', 'required' => false));
    }

    public function getName()
    {
        return 'platform_facebook_application_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'office'));
    }
}
