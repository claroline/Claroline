<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Badge\Tool;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.tool.badge.rule.tool")
 */
class BadgeRuleType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
    private $eventManager;

    /** @var  \Claroline\CoreBundle\Repository\Badge\BadgeRepository */
    private $badgeRepository;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "eventManager"          = @DI\Inject("claroline.event.manager"),
     *     "badgeRepository"       = @DI\Inject("claroline.repository.badge"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(EventManager $eventManager, BadgeRepository $badgeRepository, PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->eventManager          = $eventManager;
        $this->badgeRepository       = $badgeRepository;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actionChoices = $this->eventManager->getSortedEventsForFilter();
        /** @var \Claroline\CoreBundle\Entity\Badge\Badge[] $badgeChoices */
        $badgeChoices  = $this->badgeRepository->findOrderedByName($this->platformConfigHandler->getParameter('locale_language'));

        foreach ($badgeChoices as $badgeChoice) {
            $badgeChoice->setLocale($this->platformConfigHandler->getParameter('locale_language'));
        }

        $builder
            ->add(
                'action',
                'twolevelselect',
                array(
                    'translation_domain' => 'log',
                    'attr'               => array('class' => 'input-sm'),
                    'choices'            => $actionChoices
                )
            )
            ->add('occurrence', 'integer', array('attr' => array('class' => 'input-sm')))
            ->add('result', 'text')
            ->add(
                'badge',
                'entity',
                array(
                     'attr'        => array('class' => 'fullwidth'),
                     'class'       => 'ClarolineCoreBundle:Badge\badge',
                     'choices'     => $badgeChoices,
                     'empty_value' => '',
                     'property'    => 'name'
                )
            )
            ->add(
                'resultComparison',
                'choice',
                array('choices' => BadgeRule::getResultComparisonTypes())
            );
    }

    public function getName()
    {
        return 'badge_rule_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeRule',
                'translation_domain' => 'badge',
                'language'           => 'en'
            )
        );
    }
}
