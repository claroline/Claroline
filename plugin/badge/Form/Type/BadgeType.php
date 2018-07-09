<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Icap\BadgeBundle\Entity\Badge;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType(alias="badge_form")
 * @DI\Tag("form.type")
 */
class BadgeType extends AbstractType
{
    /** @var \Icap\BadgeBundle\Form\Type\BadgeRuleType */
    private $badgeRuleType;

    /** @var \Claroline\CoreBundle\Manager\LocaleManager */
    private $localeManager;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "localeManager"         = @DI\Inject("claroline.manager.locale_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(LocaleManager $localeManager, PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->localeManager = $localeManager;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $platformLanguage = $this->platformConfigHandler->getParameter('locale_language');
        $languages = array_values($this->localeManager->getAvailableLocales());

        usort($languages, function ($language1, $language2) use ($platformLanguage) {
            if ($language1 === $platformLanguage) {
                return -1;
            } elseif ($language2 === $platformLanguage) {
                return 1;
            } else {
                return 0;
            }
        });

        //initially, it was type 'form'
        $translationBuilder = $builder->create('translations', CollectionType::class, ['inherit_data' => true]);

        foreach ($languages as $language) {
            $fieldName = sprintf('%sTranslation', $language);
            $translationBuilder->add($fieldName, BadgeTranslationType::class);
        }

        $builder
            ->add($translationBuilder)
            ->add('automatic_award', CheckboxType::class, ['required' => false])
            ->add('file', FileType::class, ['label' => 'badge_form_image'])
            ->add('is_expiring', CheckboxType::class, ['required' => false])
            ->add('expire_duration', IntegerType::class, ['attr' => [
                      'class' => 'input-sm',
                      'min' => 1,
                ],
            ])
            ->add('expire_period', ChoiceType::class,
                [
                    'choices' => Badge::getExpirePeriodLabels(),
                    'attr' => ['class' => 'input-sm'],
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Icap\BadgeBundle\Entity\Badge $badge */
            $badge = $event->getData();

            if ($badge && null !== $badge) {
                $form = $event->getForm();
                $form
                  ->add(
                      'rules',
                      CollectionType::class,
                      [
                          'entry_type' => BadgeRuleType::class,
                          'entry_options' => ['badgeId' => $badge->getId()],
                          'by_reference' => false,
                          'attr' => ['class' => 'rule-collections'],
                          'attr' => ['label_width' => 'col-md-3'],
                          'prototype' => true,
                          'allow_add' => true,
                          'allow_delete' => true,
                      ]
                  );
            }
        });
    }

    public function getName()
    {
        return 'badge_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\BadgeBundle\Entity\Badge',
                'translation_domain' => 'icap_badge',
                'language' => 'en',
                'date_format' => DateType::HTML5_FORMAT,
                'cascade_validation' => true,
            ]
        );
    }
}
