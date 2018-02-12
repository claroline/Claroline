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

use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Form\Profile\ProfileFacetFieldsType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BaseProfileType extends AbstractType
{
    private $langs;
    private $termsOfService;
    private $facets;
    private $translator;

    public function __construct(
        LocaleManager $localeManager,
        TermsOfServiceManager $termsOfService,
        TranslatorInterface $translator,
        array $facets = []
    ) {
        $this->langs = $localeManager->retrieveAvailableLocales();
        $this->termsOfService = $termsOfService;
        $this->facets = $facets;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', 'text', ['label' => 'first_name'])
            ->add('lastName', 'text', ['label' => 'last_name'])
            ->add('username', 'text', [
                'label' => 'username',
                'attr' => [
                    'placeholder' => 'your_platform_id',
                ],
            ])
            ->add(
                'plainPassword',
                'repeated',
                [
                    'type' => 'password',
                    'first_options' => ['label' => 'password'],
                    'second_options' => [
                        'label' => 'verification',
                        'attr' => [
                            'placeholder' => 'verify_your_password',
                        ],
                    ],
                ]
            )
            ->add('email', 'email', ['label' => 'email'])
            ->add('locale', 'choice', ['choices' => $this->langs, 'required' => false, 'label' => 'language']);

        $content = $this->termsOfService->getTermsOfService(false);

        if ($this->termsOfService->isActive() && $content instanceof Content) {
            $builder->add(
                'scroll',
                'scroll',
                [
                    'label' => 'term_of_service',
                    'data' => $content->getContent(),
                ]
            )
            ->add('accepted_terms', 'checkbox', ['label' => 'terms_of_service_acceptance']);
        }

        foreach ($this->facets as $facet) {
            $type = new ProfileFacetFieldsType($facet, $this->translator);
            $type->buildForm($builder, $options);
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
            [
                'translation_domain' => 'platform',
                'validation_groups' => ['registration', 'Default'],
            ]
        );
    }
}
