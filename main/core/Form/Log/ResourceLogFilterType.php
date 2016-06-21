<?php

namespace Claroline\CoreBundle\Form\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\EventManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'choice',
                array(
                    'label' => 'Show actions for',
                    'attr' => array('class' => 'input-sm'),
                    'theme_options' => array('label_width' => 'col-md-3', 'control_width' => 'col-md-3'),
                    'choices' => $actionChoices,
                )
            )
            ->add(
                'range',
                'daterange',
                array(
                    'label' => 'for_period',
                    'required' => false,
                    'attr' => array('class' => 'input-sm'),
                    'theme_options' => array('label_width' => 'col-md-3', 'control_width' => 'col-md-3'),
                )
            )
            ->add(
                'user',
                'simpleautocomplete',
                array(
                    'label' => 'for user',
                    'entity_reference' => 'user',
                    'required' => false,
                    'attr' => array('class' => 'input-sm'),
                    'theme_options' => array('label_width' => 'col-md-3', 'control_width' => 'col-md-3'),
                )
            );
    }

    public function getName()
    {
        return '';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'log'));
    }
}
