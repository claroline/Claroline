<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class ExperienceType extends AbstractWidgetType
{
    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
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
            ->add('post', 'text')
            ->add('companyName', 'text')
            ->add('startDate', 'datepicker',
                [
                    'required' => false,
                    'language' => $language,
                    'format' => 'Y/M/d',
               ]
            )
            ->add('endDate', 'datepicker',
                [
                    'required' => false,
                    'language' => $language,
                    'format' => 'Y/M/d',
               ]
            )
            ->add('description', 'tinymce')
            ->add('website', 'url',
                [
                    'required' => false,
                ]
            );
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_experience';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\PortfolioBundle\Entity\Widget\ExperienceWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            ]
        );
    }
}
