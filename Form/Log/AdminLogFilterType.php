<?php

namespace Claroline\CoreBundle\Form\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.adminLogFilter")
 */
class AdminLogFilterType extends AbstractType
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
        $actionChoices = $this->eventManager->getSortedEventsForFilter(LogGenericEvent::DISPLAYED_ADMIN);

        $builder
            ->add(
                'action', 'twolevelselect', array(
                    'label'              => 'Show actions for',
                    'translation_domain' => 'log',
                    'attr'               => array('class' => 'input-sm'),
                    'choices'            => $actionChoices,
                    'empty_value'        => 'all',
                    'empty_data'         => null
                )
            )
            ->add(
                'range',
                'daterange',
                array(
                    'label'    => 'for period',
                    'required' => false,
                    'attr'     => array('class' => 'input-sm')
                )
            )
            ->add(
                'user',
                'simpleautocomplete',
                array(
                    'label'            => 'for user',
                    'entity_reference' => 'user',
                    'required'         => false,
                    'attr'             => array('class' => 'input-sm')
                )
            );
    }

    public function getName()
    {
        return 'admin_log_filter_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'log'
            )
        );
    }
}
