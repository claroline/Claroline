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

use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @Service("claroline.form.organization_picker")
 * @FormType(alias = "organization_picker")
 *
 * Required because the normalizer anonymous function screws up PHPMD
 * @SuppressWarnings(PHPMD)
 */
class OrganizationPickerType extends AbstractType
{
    private $resourceManager;
    private $transformer;
    private $defaultAttributes = [];

    /**
     * @InjectParams({
     *     "organizationManager" = @Inject("claroline.manager.organization.organization_manager"),
     *     "transformer"         = @Inject("claroline.transformer.organization_picker")
     * })
     */
    public function __construct($organizationManager, $transformer)
    {
        $this->organizationManager = $organizationManager;
        $this->transformer = $transformer;
    }

    public function getName()
    {
        return 'organization_picker';
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
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
    }
}
