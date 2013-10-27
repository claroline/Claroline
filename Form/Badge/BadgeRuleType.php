<?php

namespace Claroline\CoreBundle\Form\Badge;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.badgeRule")
 */
class BadgeRuleType extends AbstractType
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
        $actionChoices = $this->eventManager->getSortedEventsForFilter();

        $builder
            ->add('action', 'twolevelselect', array(
                    'translation_domain' => 'log',
                    'attr'               => array('class' => 'input-sm'),
                    'choices'            => $actionChoices
                )
            )
            ->add('occurrence', 'integer')
            ->add('result', 'text')
            ->add('resultComparison', 'choice', array(
                'choices' => BadgeRule::getResultComparisonTypes()
            ));
    }

    public function getName()
    {
        return 'badge_rule_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeRule',
                'translation_domain' => 'badge',
                'language'           => 'en'
            )
        );
    }
}
