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
use Claroline\CoreBundle\Entity\Content;

class TermsOfServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $content = '';

        if ($builder->getData() instanceof Content) {
            $content = $builder->getData()->getContent();
        }

        $builder
            ->add('scroll', 'scroll', array('label' => 'term_of_service', 'data' => $content))
            ->add('terms_of_service', 'checkbox', array('mapped' => false, 'label' => 'terms_of_service_acceptance'));
    }

    public function getName()
    {
        return 'accept_terms_of_service_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform', 'validation_groups' => array('registration', 'Default'))
        );
    }
}
