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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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

        $builder->add('name', TextType::class, ['label' => 'name']);
        $builder->add(
            'newIcon',
            FileType::class,
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
            CheckboxType::class,
            ['required' => true, 'label' => 'published']
        );
        $builder->add(
            'publishedToPortal',
            CheckboxType::class,
            ['required' => false, 'label' => 'published_to_portal']
        );
        $builder->add(
            HiddenType::class,
            CheckboxType::class,
            ['label' => 'hidden']
        );
        $builder->add('description', TextareaType::class, [
            'label' => 'description',
            'attr' => [
                'class' => 'form-control',
            ],
        ]);
        $builder->add('newThumbnail', FileType::class,
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
            TextType::class,
            [
                'data' => $this->creator,
                'mapped' => false,
                'disabled' => true,
                'label' => 'creator',
            ]
        );
        $builder->add(
            'license',
            TextType::class,
            [
                'label' => 'license',
                'required' => false,
            ]
        );
        $builder->add(
            'author',
            TextType::class,
            [
                'label' => 'author',
                'required' => false,
            ]
        );
        $builder->add(
            'deletable',
            CheckboxType::class,
            ['required' => true, 'label' => 'deletable']
        );
    }

    public function getName()
    {
        return 'resource_properties_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
