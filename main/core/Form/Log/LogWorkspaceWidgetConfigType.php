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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

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
            ->add(
                'restrictions',
                'select2',
                array(
                    'choices' => $actionChoices,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'translation_domain' => 'log',
                    'attr' => array('placeholder' => 'click_to_choose'),
                )
            )
            ->add(
                'amount',
                'choice',
                array(
                    'choices' => array(
                        '1' => '1',
                        '5' => '5',
                        '10' => '10',
                        '15' => '15',
                        '20' => '20',
                    ),
                    'required' => true,
                )
            );
    }

    public function getName()
    {
        return 'log_widget_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'log',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ));
    }
}
