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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @Service()
 * @FormType(alias = "resourcePicker")
 */
class ResourcePickerType extends TextType
{
    private $resourceManager;

    /**
     * @InjectParams({
     *     "resourceManager" = @Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct($resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    public function getName()
    {
        return 'resourcePicker';
    }

    public function getParent()
    {
        return 'text';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'label' => 'resource',
                'attr' => array('class' => 'hide resource-picker')
            )
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        if ($resourceNode = $this->getResourceNode($form)) {
            $view->vars['attr']['data-name'] = $resourceNode->getName();
            $view->vars['attr']['data-type'] = $resourceNode->getResourceType()->getName();
        }
    }

    /**
     * Get resource node
     */
    private function getResourceNode(FormInterface $form)
    {
        if ($form->getData() instanceof ResourceNode) {
            return $form->getData();
        } else if ($form->getData() and $form->getData() != '') {
            return $this->resourceManager->getById($form->getData());
        }
    }
}
