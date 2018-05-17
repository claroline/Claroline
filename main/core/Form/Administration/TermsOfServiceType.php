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

use Claroline\CoreBundle\Form\Field\ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermsOfServiceType extends AbstractType
{
    private $active;
    private $lockedParams;

    public function __construct($active = false, array $lockedParams = [])
    {
        $this->active = $active;
        $this->lockedParams = $lockedParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'termsOfService',
            ContentType::class,
            [
                'required' => false,
                'data' => $builder->getData(),
                'theme_options' => ['contentTitle' => false],
                'label' => 'term_of_service',
            ]
        )
        ->add(
            'active',
            CheckboxType::class,
            [
                'required' => false,
                'mapped' => false,
                'data' => $this->active,
                'label' => 'term_of_service_activation_message',
                'disabled' => isset($this->lockedParams['terms_of_service']),
            ]
        );
    }

    public function getName()
    {
        return 'terms_of_service_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
