<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;

class BaseProfileType extends AbstractType
{
    private $langs;
    private $termsOfService;

    public function __construct(LocaleManager $localeManager, TermsOfServiceManager $termsOfService)
    {
        $this->langs = $localeManager->getAvailableLocales();
        $this->termsOfService = $termsOfService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', 'text')
            ->add('lastName', 'text')
            ->add('username', 'text')
            ->add('plainPassword', 'repeated', array('type' => 'password', 'invalid_message' => 'password_mismatch'))
            ->add('mail', 'email')
            ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'));

        if ($this->termsOfService->isActive() and $this->termsOfService->getTermsOfService()) {

            $builder->add(
                'test',
                'textarea',
                array(
                    'attr' => array('class' => 'form-control', 'rows' => '10'),
                    'mapped' => false,
                    'label' => 'Terms of service',
                    'read_only' => true,
                    'data' => $this->termsOfService->getTermsOfService()
                )
            )
            ->add('terms_of_service', 'checkbox', array('label' => 'I accept the terms of service'));
        }
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                'validation_groups' => array('registration', 'Default')
            )
        );
    }
}
