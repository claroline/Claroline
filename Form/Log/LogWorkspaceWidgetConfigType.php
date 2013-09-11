<?php

namespace Claroline\CoreBundle\Form\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.logWorkspaceWidgetConfig")
 */
class LogWorkspaceWidgetConfigType extends AbstractType
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
        $actionChoices = $this->eventManager->getSortedEventsForConfigForm(LogGenericEvent::DISPLAYED_WORKSPACE);

        $builder
            ->add('restrictions', 'choice', array(
                'choices'   => $actionChoices,
                'required'  => false,
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add(
                'amount',
                'choice',
                array(
                    'choices' => array(
                        '1' => '1',
                        '5' => '5',
                        '10' => '10',
                        '15' => '15',
                        '20' => '20'
                    ),
                    'required' => true
                )
            );
    }

    public function getName()
    {
        return 'log_widget_config';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'translation_domain' => 'log'
            )
        );
    }
}
