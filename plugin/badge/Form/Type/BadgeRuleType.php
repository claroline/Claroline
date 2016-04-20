<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\EventManager;
use Icap\BadgeBundle\Entity\BadgeRule;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.form.badge.rule")
 */
class BadgeRuleType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
    private $eventManager;

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /** @var int */
    private $badgeId;

    /**
     * @DI\InjectParams({
     *     "eventManager" = @DI\Inject("claroline.event.manager"),
     *     "translator" = @DI\Inject("translator"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(EventManager $eventManager, TranslatorInterface $translator,
        PlatformConfigurationHandler $platformConfigHandler, TokenStorageInterface $tokenStorage)
    {
        $this->eventManager = $eventManager;
        $this->translator = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actionChoices = $this->eventManager->getSortedEventsForFilter();

        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $locale = (null === $user->getLocale()) ? $this->platformConfigHandler->getParameter('locale_language') : $user->getLocale();

        $builder
            ->add(
                'action',
                'twolevelselect',
                array(
                    'translation_domain' => 'log',
                    'attr' => array('class' => 'input-sm'),
                    'choices' => $actionChoices,
                    'choices_as_values' => true,
                )
            )
            ->add('isUserReceiver', 'checkbox')
            ->add('occurrence', 'integer', array('attr' => array('class' => 'input-sm')))
            ->add('result', 'text')
            ->add('resource', 'resourcePicker', array(
                    'required' => false,
                )
            )
            ->add(
                'resultComparison',
                'choice',
                array('choices' => BadgeRule::getResultComparisonTypes())
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $blacklist = array();

        if (null !== $this->badgeId) {
            array_push($blacklist, $this->badgeId);
        }

        $form
            ->add('badge', 'badgepicker', array(
                'blacklist' => $blacklist,
            )
        );
    }

    /**
     * @param int $badgeId
     *
     * @return BadgeRuleType
     */
    public function setBadgeId($badgeId)
    {
        $this->badgeId = $badgeId;

        return $this;
    }

    public function getName()
    {
        return 'badge_rule_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeRule',
                'translation_domain' => 'icap_badge',
                'language' => 'en',
            )
        );
    }
}
