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
        $dateFormat = $this->translator->trans('date_form_format', array(), 'platform');
        $attrParams = array(
                'class' => 'datepicker input-small',
                'data-date-format' => $this->translator->trans('date_form_datepicker_format', array(), 'platform'),
                'autocomplete' => 'off',
            );

        $dateParams = array(
            'format' => $dateFormat,
            'widget' => 'single_text',
            'input' => 'datetime',
            'attr' => $attrParams,
        );

        $builder->add('name', 'text', array('label' => 'name'));
        $builder->add(
            'newIcon',
            'file',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'icon',
            )
        );
        $builder->add(
            'creationDate',
            'date',
            array(
                'disabled' => true,
                'widget' => 'single_text',
                'format' => $dateFormat,
                'label' => 'creation_date',
            )
        );
        $builder->add(
            'modificationDate',
            'date',
            array(
                'disabled' => true,
                'widget' => 'single_text',
                'format' => $dateFormat,
                'label' => 'last_modification',
            )
        );
        $builder->add(
            'published',
            'checkbox',
            array('required' => true, 'label' => 'published')
        );
        $builder->add(
            'publishedToPortal',
            'checkbox',
            array('required' => false, 'label' => 'published_to_portal')
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
            array(
                'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceType',
                'choice_translation_domain' => true,
                'translation_domain' => 'resource',
                'expanded' => false,
                'multiple' => false,
                'property' => 'name',
                'disabled' => true,
                'label' => 'resource_type',
            )
        );
        $builder->add(
            'creator',
            'text',
            array(
                'data' => $this->creator,
                'mapped' => false,
                'disabled' => true,
                'label' => 'creator',
            )
        );
        $builder->add(
            'license',
            'text',
            array(
                'label' => 'license',
                'required' => false,
            )
        );
        $builder->add(
            'author',
            'text',
            array(
                'label' => 'author',
                'required' => false,
            )
        );
    }

    public function getName()
    {
        return 'resource_properties_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
