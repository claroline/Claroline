<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Form;

use Claroline\ClacoFormBundle\Entity\ClacoFormWidgetConfig;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class ClacoFormWidgetConfigurationType extends AbstractType
{
    private $config;
    private $clacoFormManager;

    public function __construct(ClacoFormWidgetConfig $config, ClacoFormManager $clacoFormManager)
    {
        $this->config = $config;
        $this->clacoFormManager = $clacoFormManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nbEntries = $this->config->getNbEntries();
        $showFieldLabel = $this->config->getShowFieldLabel();
        $showCreatorPicture = $this->config->getShowCreatorPicture();

        $builder->add(
            'nbEntries',
            'integer',
            [
                'mapped' => false,
                'data' => $nbEntries,
                'required' => true,
                'constraints' => [new Range(['min' => 0])],
                'attr' => ['min' => 0],
                'label' => 'nb_entries',
            ]
        );
        $builder->add(
            'showCreatorPicture',
            'checkbox',
            [
                'mapped' => false,
                'data' => $showCreatorPicture,
                'label' => 'show_creator_picture',
            ]
        );
        $builder->add(
            'showFieldLabel',
            'checkbox',
            [
                'mapped' => false,
                'data' => $showFieldLabel,
                'label' => 'show_field_label',
            ]
        );
        $builder->add(
            'resourceNode',
            'resourcePicker',
            [
                'attr' => [
                    'data-is-picker-multi-select-allowed' => 0,
                    'data-is-directory-selection-allowed' => 0,
                    'data-type-white-list' => 'claroline_claco_form',
                ],
                'display_browse_button' => false,
                'display_download_button' => false,
                'label' => 'claroline_claco_form',
                'translation_domain' => 'resource',
            ]
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $clacoFormWidgetConfig = $event->getData();
                $resourceNode = $clacoFormWidgetConfig->getResourceNode();
                $form->add(
                    'fields',
                    'entity',
                    [
                        'class' => 'ClarolineClacoFormBundle:Field',
                        'property' => 'name',
                        'required' => false,
                        'multiple' => true,
                        'label' => 'fields_to_display',
                        'query_builder' => function (EntityRepository $er) use ($resourceNode) {
                            return $er->createQueryBuilder('f')
                                ->join('f.clacoForm', 'c')
                                ->join('c.resourceNode', 'r')
                                ->where('r = :resourceNode')
                                ->andWhere('f.isMetadata = false')
                                ->setParameter('resourceNode', $resourceNode);
                        },
                    ]
                );
            }
        );
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $resourceNodeId = intval($data['resourceNode']);
                $form->add(
                    'fields',
                    'entity',
                    [
                        'class' => 'ClarolineClacoFormBundle:Field',
                        'property' => 'name',
                        'multiple' => true,
                        'label' => 'fields_to_display',
                        'query_builder' => function (EntityRepository $er) use ($resourceNodeId) {
                            return $er->createQueryBuilder('f')
                                ->join('f.clacoForm', 'c')
                                ->join('c.resourceNode', 'r')
                                ->where('r.id = :resourceNodeId')
                                ->andWhere('f.isMetadata = false')
                                ->setParameter('resourceNodeId', $resourceNodeId);
                        },
                    ]
                );
            }
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $clacoFormWidgetConfig = $event->getData();
                $resourceNode = $clacoFormWidgetConfig->getResourceNode();

                if (empty($resourceNode)) {
                    $displayCategories = false;
                    $categories = [];
                } else {
                    $clacoForm = $this->clacoFormManager->getClacoFormByResourceNode($resourceNode);
                    $displayCategories = $clacoForm->getDisplayCategories();
                    $categoriesIds = $this->config->getCategories();
                    $categories = $displayCategories ? $this->clacoFormManager->getCategoriesByIds($categoriesIds) : [];
                }

                $form->add(
                    'categories',
                    'entity',
                    [
                        'class' => 'ClarolineClacoFormBundle:Category',
                        'mapped' => false,
                        'property' => 'name',
                        'required' => false,
                        'multiple' => true,
                        'data' => $categories,
                        'label' => 'categories_to_filter',
                        'query_builder' => function (EntityRepository $er) use ($resourceNode, $displayCategories) {
                            return $displayCategories ?
                                $er->createQueryBuilder('cat')
                                    ->join('cat.clacoForm', 'c')
                                    ->join('c.resourceNode', 'r')
                                    ->where('r = :resourceNode')
                                    ->setParameter('resourceNode', $resourceNode) :
                                $er->createQueryBuilder('cat')
                                    ->where('cat.id = -1');
                        },
                    ]
                );
            }
        );
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $resourceNodeId = intval($data['resourceNode']);
                $clacoForm = $this->clacoFormManager->getClacoFormByResourceNodeId($resourceNodeId);
                $displayCategories = $clacoForm->getDisplayCategories();
                $categoriesIds = $this->config->getCategories();
                $categories = $displayCategories ? $this->clacoFormManager->getCategoriesByIds($categoriesIds) : [];

                $form->add(
                    'categories',
                    'entity',
                    [
                        'class' => 'ClarolineClacoFormBundle:Category',
                        'mapped' => false,
                        'property' => 'name',
                        'multiple' => true,
                        'data' => $categories,
                        'label' => 'categories_to_filter',
                        'query_builder' => function (EntityRepository $er) use ($resourceNodeId, $displayCategories) {
                            return $displayCategories ?
                                $er->createQueryBuilder('cat')
                                    ->join('cat.clacoForm', 'c')
                                    ->join('c.resourceNode', 'r')
                                    ->where('r.id = :resourceNodeId')
                                    ->setParameter('resourceNodeId', $resourceNodeId) :
                                $er->createQueryBuilder('cat')
                                    ->where('cat.id = -1');
                        },
                    ]
                );
            }
        );
    }

    public function getName()
    {
        return 'claco_form_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'clacoform']);
    }
}
