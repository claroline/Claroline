<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class UserInformationType extends AbstractWidgetType
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
            ->add('birthDate', 'datepicker',
                [
                    'required' => false,
                    'language' => $language,
                    'format' => 'Y/M/d',
               ]
            )
            ->add('city', TextType::class)
            ->add('show_avatar', CheckboxType::class, [
                'required' => false,
            ])
            ->add('show_mail', CheckboxType::class, [
                'required' => false,
            ])
            ->add('show_phone', CheckboxType::class, [
                'required' => false,
            ])
            ->add('show_description', CheckboxType::class, [
                'required' => false,
            ])
            ->add(TextType::class, 'tinymce');
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_userInformation';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\PortfolioBundle\Entity\Widget\UserInformationWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            ]
        );
    }
}
