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
use Claroline\CoreBundle\Form\Field\ScrollType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermsOfServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $content = '';

        if ($data instanceof Content) {
            $content = $data->getContent();
        } elseif (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
        }

        $builder
            ->add('scroll', ScrollType::class, ['label' => 'term_of_service', 'data' => $content])
            ->add('terms_of_service', CheckboxType::class, ['mapped' => false, 'label' => 'terms_of_service_acceptance']);
    }

    public function getName()
    {
        return 'accept_terms_of_service_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['translation_domain' => 'platform', 'validation_groups' => ['registration', 'Default']]
        );
    }
}
