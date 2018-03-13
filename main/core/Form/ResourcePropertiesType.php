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
use Symfony\Component\Translation\TranslatorInterface;

class ResourcePropertiesType extends AbstractType
{
    private $creator;
    private $translator;

    public function __construct($creator, TranslatorInterface $translator)
    {
        $this->creator = $creator;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateFormat = $this->translator->trans('date_form_format', [], 'platform');
        $attrParams = [
                'class' => 'datepicker input-small',
                'data-date-format' => $this->translator->trans('date_form_datepicker_format', [], 'platform'),
                'autocomplete' => 'off',
            ];

        $dateParams = [
            'format' => $dateFormat,
            'widget' => 'single_text',
            'input' => 'datetime',
            'attr' => $attrParams,
        ];

        $builder->add('name', 'text', ['label' => 'name']);
        $builder->add(
            'newIcon',
            'file',
            [
                'required' => false,
                'mapped' => false,
                'label' => 'icon',
            ]
        );
        $builder->add(
            'creationDate',
            'date',
            [
                'disabled' => true,
                'widget' => 'single_text',
                'format' => $dateFormat,
                'label' => 'creation_date',
            ]
        );
        $builder->add(
            'modificationDate',
            'date',
            [
                'disabled' => true,
                'widget' => 'single_text',
                'format' => $dateFormat,
                'label' => 'last_modification',
            ]
        );
        $builder->add(
            'published',
            'checkbox',
            ['required' => true, 'label' => 'published']
        );
        $builder->add(
            'publishedToPortal',
            'checkbox',
            ['required' => false, 'label' => 'published_to_portal']
        );
        $builder->add('description', 'textarea', [
            'label' => 'description',
            'attr' => [
                'class' => 'form-control',
            ],
        ]);
        $builder->add('newThumbnail', 'file',
            [
                'required' => false,
                'mapped' => false,
                'label' => $this->translator->trans('thumbnail', [], 'platform'),
            ]
        );
        $accessibleFromParams = $dateParams;
        $accessibleFromParams['label'] = 'accessible_from';
        $builder->add('accessibleFrom', 'datepicker', $accessibleFromParams);
        $accessibleUntilParams = $dateParams;
        $accessibleUntilParams['label'] = 'accessible_until';
        $builder->add('accessibleUntil', 'datepicker', $accessibleUntilParams);
        $builder->add(
            'resourceType',
            'entity',
            [
                'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceType',
                'choice_translation_domain' => true,
                'translation_domain' => 'resource',
                'expanded' => false,
                'multiple' => false,
                'property' => 'name',
                'disabled' => true,
                'label' => 'resource_type',
            ]
        );
        $builder->add(
            'creator',
            'text',
            [
                'data' => $this->creator,
                'mapped' => false,
                'disabled' => true,
                'label' => 'creator',
            ]
        );
        $builder->add(
            'license',
            'text',
            [
                'label' => 'license',
                'required' => false,
            ]
        );
        $builder->add(
            'author',
            'text',
            [
                'label' => 'author',
                'required' => false,
            ]
        );
        $builder->add(
            'deletable',
            'checkbox',
            ['required' => true, 'label' => 'deletable']
        );
    }

    public function getName()
    {
        return 'resource_properties_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
