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

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldFacetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'name']);
        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    FieldFacet::STRING_TYPE => 'text',
                    FieldFacet::NUMBER_TYPE => 'number',
                    FieldFacet::DATE_TYPE => 'date',
                ],
                'multiple' => false,
                'expanded' => false,
                'label' => 'type',
            ]
        );
        $builder->add(
            'isVisibleByOwner',
            'choice', [
                'choices' => ['1' => 'yes', '0' => 'no'],
                'label' => 'visible',
                'expanded' => false,
                'multiple' => false,
            ]
        );
        $builder->add(
            'isEditableByOwner',
            'choice', [
                'choices' => ['1' => 'yes', '0' => 'no'],
                'label' => 'editable',
                'expanded' => false,
                'multiple' => false,
            ]
        );
    }

    public function getName()
    {
        return 'facet_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
