<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Badge\Type\Tool\Workspace;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.form.badge.workspace.rule")
 */
class BadgeRuleType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
    private $eventManager;

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface */
    private $securityContext;

    /** @var integer */
    public $workspace;

    /**
     * @DI\InjectParams({
     *     "eventManager"          = @DI\Inject("claroline.event.manager"),
     *     "translator"            = @DI\Inject("translator"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "securityContext"       = @DI\Inject("security.context")
     * })
     */
    public function __construct(EventManager $eventManager, TranslatorInterface $translator,
        PlatformConfigurationHandler $platformConfigHandler, SecurityContextInterface $securityContext)
    {
        $this->eventManager          = $eventManager;
        $this->translator            = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->securityContext       = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actionChoices = $this->eventManager->getSortedEventsForFilter();

        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();

        $locale = (null === $user->getLocale()) ? $this->platformConfigHandler->getParameter("locale_language") : $user->getLocale();

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
            ->add('isUserReceiver', 'checkbox')
            ->add('occurrence', 'integer', array('attr' => array('class' => 'input-sm')))
            ->add('result', 'text')
            ->add('resource', 'zenstruck_ajax_entity', array(
                'attr'           => array('class' => 'fullwidth'),
                'placeholder'    => $this->translator->trans('badge_form_resource_selection', array(), 'badge'),
                'class'          => 'ClarolineCoreBundle:Resource\ResourceNode',
                'use_controller' => true,
                'property'       => 'pathForDisplay',
                'repo_method'    => 'findByNameForAjax',
                'extra_data'     => array('workspace' => $this->workspace)
            ))
            ->add('badge', 'zenstruck_ajax_entity', array(
                'attr'           => array('class' => 'fullwidth'),
                'placeholder'    => $this->translator->trans('badge_form_badge_selection', array(), 'badge'),
                'class'          => 'ClarolineCoreBundle:Badge\Badge',
                'use_controller' => true,
                'property'       => sprintf("%sName", $locale),
                'repo_method'    => sprintf('findByName%sForAjax', ucfirst($locale))
            ))
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
