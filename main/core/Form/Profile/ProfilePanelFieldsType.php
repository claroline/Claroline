<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Profile;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilePanelFieldsType extends AbstractType
{
    public function __construct(
        PanelFacet $panel,
        TranslatorInterface $translator
    ) {
        $this->panel = $panel;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateAttr = [];
        $dateAttr['class'] = 'datepicker input-small';
        $dateAttr['data-date-format'] = $this->translator->trans('date_form_datepicker_format', [], 'platform');
        $dateAttr['autocomplete'] = 'off';

        foreach ($this->panel->getFieldsFacet() as $field) {
            $constraints = $field->isRequired() ? [new NotBlank()] : [];

            switch ($field->getType()) {
                case FieldFacet::STRING_TYPE:
                    $builder->add(
                        $field->getPrettyName(),
                        'text',
                        [
                            'label' => $this->translator->trans($field->getName(), [], 'platform'),
                            'mapped' => false,
                            'required' => false,
                            'attr' => ['facet' => $this->panel->getFacet()->getName()],
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case FieldFacet::EMAIL_TYPE:
                    $builder->add(
                        $field->getPrettyName(),
                        'email',
                        [
                            'label' => $this->translator->trans($field->getName(), [], 'platform'),
                            'mapped' => false,
                            'required' => false,
                            'attr' => ['facet' => $this->panel->getFacet()->getName()],
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case FieldFacet::DATE_TYPE:
                    $builder->add(
                        $field->getPrettyName(),
                        'datepicker',
                        [
                            'label' => $this->translator->trans($field->getName(), [], 'platform'),
                            'required' => false,
                            'widget' => 'single_text',
                            'format' => $this->translator->trans('date_form_datepicker_format', [], 'platform'),
                            'attr' => $dateAttr,
                            'autoclose' => true,
                            'mapped' => false,
                            'attr' => ['facet' => $this->panel->getFacet()->getName()],
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case FieldFacet::FLOAT_TYPE:
                    $builder->add(
                        $field->getPrettyName(),
                        'number',
                        [
                            'label' => $this->translator->trans($field->getName(), [], 'platform'),
                            'mapped' => false,
                            'required' => false,
                            'attr' => ['facet' => $this->panel->getFacet()->getName()],
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case FieldFacet::COUNTRY_TYPE:
                        $builder->add(
                            $field->getPrettyName(),
                            'country',
                            [
                                'label' => $this->translator->trans($field->getName(), [], 'platform'),
                                'mapped' => false,
                                'required' => false,
                                'attr' => ['facet' => $this->panel->getFacet()->getName()],
                                'constraints' => $constraints,
                            ]
                        );
                        break;
                default:
                    $choices = $field->getFieldFacetChoices();

                    $attrs = [];
                    foreach ($choices as $choice) {
                        $attrs[$choice->getLabel()] = $choice->getLabel();
                    }

                    switch ($field->getType()) {
                        case FieldFacet::RADIO_TYPE:
                            $multiple = false;
                            $expanded = true;
                            break;
                        case FieldFacet::SELECT_TYPE:
                            $multiple = false;
                            $expanded = false;
                            break;
                        case FieldFacet::CHECKBOXES_TYPE:
                            $multiple = true;
                            $expanded = true;
                            break;
                    }

                    $builder->add(
                        $field->getPrettyName(),
                        'choice',
                        [
                            'choices' => $attrs,
                            'label' => $this->translator->trans($field->getName(), [], 'platform'),
                            'mapped' => false,
                            'required' => false,
                            'attr' => ['facet' => $this->panel->getFacet()->getName()],
                            'choices_as_values' => true,
                            'expanded' => $expanded,
                            'multiple' => $multiple,
                            'constraints' => $constraints,
                        ]
                    );
                }
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
