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

use Claroline\CoreBundle\Form\Angular\AngularType;
use Claroline\CoreBundle\Repository\WidgetRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WidgetInstanceConfigType extends AngularType
{
    private $bundles;
    private $color;
    private $creationMode;
    private $forApi = false;
    private $locked;
    private $ngAlias;
    private $roles;
    private $textTitleColor;
    private $type;
    private $visible;
    private $withRole;

    public function __construct(
        $type = 'desktop',
        array $bundles = [],
        $withRole = false,
        array $roles = [],
        $color = null,
        $textTitleColor = null,
        $locked = false,
        $visible = true,
        $creationMode = true,
        $ngAlias = 'wfmc'
    ) {
        $this->bundles = $bundles;
        $this->color = $color;
        $this->creationMode = $creationMode;
        $this->locked = $locked;
        $this->ngAlias = $ngAlias;
        $this->roles = $roles;
        $this->textTitleColor = $textTitleColor;
        $this->type = $type;
        $this->visible = $visible;
        $this->withRole = $withRole;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'label' => 'name',
                'constraints' => new NotBlank(),
            ]
        );

        if ($this->creationMode) {
            $datas = [];
            $datas['is_desktop'] = $this->type === 'desktop' || $this->type === 'admin';
            $datas['with_role'] = $this->withRole;
            $datas['roles'] = $this->roles;
            $datas['bundles'] = $this->bundles;

            $builder->add(
                'widget',
                'entity',
                [
                    'label' => 'type',
                    'class' => 'Claroline\CoreBundle\Entity\Widget\Widget',
                    'choice_translation_domain' => true,
                    'translation_domain' => 'widget',
                    'expanded' => false,
                    'multiple' => false,
                    'constraints' => new NotBlank(),
                    'query_builder' => function (WidgetRepository $widgetRepo) use ($datas) {
                        if ($datas['is_desktop']) {
                            if ($datas['with_role']) {
                                return $widgetRepo->createQueryBuilder('w')
                                    ->join('w.roles', 'r')
                                    ->where('w.isDisplayableInDesktop = true')
                                    ->andWhere('r IN (:roles)')
                                    ->leftJoin('w.plugin', 'p')
                                    ->andWhere('CONCAT(p.vendorName, p.bundleName) IN (:bundles) OR w.plugin is null')
                                    ->setParameter('roles', $datas['roles'])
                                    ->setParameter('bundles', $datas['bundles']);
                            } else {
                                return $widgetRepo->createQueryBuilder('w')
                                    ->where('w.isDisplayableInDesktop = true')
                                    ->leftJoin('w.plugin', 'p')
                                    ->andWhere('CONCAT(p.vendorName, p.bundleName) IN (:bundles) OR w.plugin is null')
                                    ->setParameter('bundles', $datas['bundles']);
                            }
                        } else {
                            return $widgetRepo->createQueryBuilder('w')
                                ->where('w.isDisplayableInWorkspace = true')
                                ->leftJoin('w.plugin', 'p')
                                ->andWhere('CONCAT(p.vendorName, p.bundleName) IN (:bundles) OR w.plugin is null')
                                ->setParameter('bundles', $datas['bundles']);
                        }
                    },
                ]
            );
        }

        if ($this->type === 'admin') {
            $builder->add(
                'visible',
                'choice',
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'visible',
                    'required' => true,
                    'mapped' => false,
                    'choices_as_values' => true,
                    'data' => $this->visible,
                ]
            );
            $builder->add(
                'locked',
                'choice',
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'locked',
                    'mapped' => false,
                    'required' => true,
                    'choices_as_values' => true,
                    'data' => $this->locked,
                ]
            );
        } elseif ($this->type === 'workspace') {
            $builder->add(
                'visible',
                'choice',
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'visible',
                    'required' => true,
                    'mapped' => false,
                    'choices_as_values' => true,
                    'data' => $this->visible,
                ]
            );
        }

        $builder->add(
            'color',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'label' => 'color',
                'data' => $this->color,
                'attr' => ['colorpicker' => 'hex'],
            ]
        );
        $builder->add(
            'textTitleColor',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'label' => 'text_title_color',
                'data' => $this->textTitleColor,
                'attr' => ['colorpicker' => 'hex'],
            ]
        );
    }

    public function getName()
    {
        return 'widget_instance_config_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = ['translation_domain' => 'platform'];

        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $default['ng-model'] = 'widgetInstance';
        $default['ng-controllerAs'] = $this->ngAlias;

        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
