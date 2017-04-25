<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @Service()
 * @FormType(alias = "resourcePicker")
 *
 * Required because the normalizer anonymous function screws up PHPMD
 * @SuppressWarnings(PHPMD)
 */
class ResourcePickerType extends TextType
{
    private $resourceManager;
    private $transformer;
    private $defaultAttributes = ['class' => 'hide resource-picker'];

    /**
     * @InjectParams({
     *     "resourceManager" = @Inject("claroline.manager.resource_manager"),
     *     "transformer" = @Inject("claroline.transformer.resource_picker")
     * })
     */
    public function __construct($resourceManager, $transformer)
    {
        $this->resourceManager = $resourceManager;
        $this->transformer = $transformer;
    }

    public function getName()
    {
        return 'resourcePicker';
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['display_view_button'] = $options['display_view_button'];
        $view->vars['display_browse_button'] = $options['display_browse_button'];
        $view->vars['display_download_button'] = $options['display_download_button'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'label' => 'resource',
                'attr' => $this->defaultAttributes,
                'display_view_button' => true,
                'display_browse_button' => true,
                'display_download_button' => true,
            ]
        );

        $resolver
            ->setNormalizer(
                'attr',
                function (Options $options, $value) {
                    return array_merge($this->defaultAttributes, $value);
                }
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        if ($resourceNode = $this->getResourceNode($form)) {
            $view->vars['attr']['data-name'] = $resourceNode->getName();
            $view->vars['attr']['data-type'] = $resourceNode->getResourceType()->getName();
            $view->vars['attr']['value'] = $resourceNode->getId();
        }
    }

    /**
     * Get resource node.
     */
    private function getResourceNode(FormInterface $form)
    {
        if ($form->getData() instanceof ResourceNode) {
            return $form->getData();
        } elseif ($form->getData() && $form->getData() !== '') {
            return $this->resourceManager->getById($form->getData());
        }
    }
}
