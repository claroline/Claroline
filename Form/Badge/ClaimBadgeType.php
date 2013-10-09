<?php

namespace Claroline\CoreBundle\Form\Badge;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.claimBadge")
 */
class ClaimBadgeType extends AbstractType
{
    /** @var PlatformConfigurationHandler */
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
        $builder
            ->add('badge', 'simpleautocomplete', array(
                'entity_reference' => 'badge',
                'required'         => false,
                'format'           => 'jsonp',
                'mapped'           => false,
                'extraDatas'       => array('locale' => $this->platformConfigHandler->getParameter('locale_language'))
            ));
    }

    public function getName()
    {
        return 'badge_claim_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeClaim',
                    'translation_domain' => 'badge'
                )
            );
    }
}
