<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Form\Field\ResourcePickerType;
use Claroline\CoreBundle\Form\Field\TwoLevelSelectType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\EventManager;
use Icap\BadgeBundle\Entity\BadgeRule;
use Icap\BadgeBundle\Form\Field\BadgePickerType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.form.badge.rule")
 * @DI\Tag("form.type")
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

        $builder
            ->add(
                'action',
                TwoLevelSelectType::class,
                [
                    'translation_domain' => 'log',
                    'attr' => ['class' => 'input-sm'],
                    'choices' => $actionChoices,
                    'choices_as_values' => true,
                ]
            )
            ->add('isUserReceiver', CheckboxType::class)
            ->add('occurrence', IntegerType::class, ['attr' => ['class' => 'input-sm']])
            ->add('result', TextType::class)
            ->add('resource', ResourcePickerType::class, ['required' => false])
            ->add(
                'resultComparison',
                ChoiceType::class,
                ['choices' => BadgeRule::getResultComparisonTypes()]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            $blacklist = [];

            if (null !== $options['badgeId']) {
                array_push($blacklist, $options['badgeId']);
            }

            $form
                ->add('badge', BadgePickerType::class, [
                    'blacklist' => $blacklist,
                ]
            );
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeRule',
                'translation_domain' => 'icap_badge',
                'language' => 'en',
                'badgeId' => null,
            ]
        );
    }
}
