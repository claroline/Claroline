<?php

namespace Claroline\CoreBundle\Form\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("claroline.form.resourceLogFilter")
 */
class ResourceLogFilterType extends AbstractType
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
        $actionChoices = $this->eventManager->getResourceEventsForFilter(
            LogGenericEvent::DISPLAYED_WORKSPACE,
            $options['data']['resourceClass']
        );

        $builder
            ->add(
                'action',
                ChoiceType::class,
                [
                    'label' => 'Show actions for',
                    'attr' => ['class' => 'input-sm'],
                    'attr' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                    'choices' => $actionChoices,
                ]
            )
            ->add(
                'range',
                'daterange',
                [
                    'label' => 'for_period',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'attr' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            )
            ->add(
                'user',
                'simpleautocomplete',
                [
                    'label' => 'for user',
                    'entity_reference' => 'user',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'attr' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            )
            ->add(
                'group',
                'simpleautocomplete',
                [
                    'label' => 'for group',
                    'entity_reference' => 'group',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'attr' => ['label_width' => 'col-md-3', 'control_width' => 'col-md-3'],
                ]
            );
    }

    public function getName()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'log']);
    }
}
