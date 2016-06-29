<?php

namespace Icap\BadgeBundle\Form\Type\Portfolio;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class BadgesType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler  */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $language = $this->platformConfigHandler->getParameter('locale_language');

        $builder
            ->add('label', 'text')
            ->add('children', 'collection',
                array(
                    'type' => 'icap_badge_portfolio_widget_form_badges_badge',
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'property_path' => 'badges',
                )
            );
    }

    public function getName()
    {
        return 'icap_badge_portfolio_widget_form_badges';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\Portfolio\BadgesWidget',
                'translation_domain' => 'icap_badge',
                'csrf_protection' => false,
            )
        );
    }
}
