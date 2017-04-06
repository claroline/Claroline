<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.workspaceLogFilter")
 */
class WorkspaceLogFilterType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
    private $eventManager;

    /**
     * @DI\InjectParams({
     *     "eventManager" = @DI\Inject("claroline.event.manager")
     * })
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actionChoices = $this->eventManager->getSortedEventsForFilter(LogGenericEvent::DISPLAYED_WORKSPACE);

        $builder
            ->add(
                'action', 'twolevelselect', [
                    'label' => 'Show actions for',
                    'translation_domain' => 'log',
                    'attr' => ['class' => 'input-sm'],
                    'choices' => $actionChoices,
                    'choices_as_values' => true,
                    'empty_value' => 'all',
                    'empty_data' => null,
                ]
            )
            ->add(
                'range', 'daterange', [
                    'label' => 'for_period',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'theme_options' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            )
            ->add(
                'user', 'simpleautocomplete', [
                    'label' => 'for user',
                    'entity_reference' => 'user',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'theme_options' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            )
            ->add(
                'group', 'simpleautocomplete', [
                    'label' => 'for group',
                    'entity_reference' => 'group',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'theme_options' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            );
    }

    public function getName()
    {
        return '';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            [
                'translation_domain' => 'log',
                'csrf_protection' => false,
            ]
        );
    }
}
