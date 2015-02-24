<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.form.claimBadge")
 */
class ClaimBadgeType extends AbstractType
{
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface */
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "translator"            = @DI\Inject("translator"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "securityContext"       = @DI\Inject("security.context")
     * })
     */
    public function __construct(TranslatorInterface $translator, PlatformConfigurationHandler $platformConfigHandler, SecurityContextInterface $securityContext)
    {
        $this->translator            = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->securityContext       = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();

        $locale = (null === $user->getLocale()) ? $this->platformConfigHandler->getParameter("locale_language") : $user->getLocale();

        $builder
            ->add('badge', 'zenstruck_ajax_entity', array(
                'attr'           => array('class' => 'fullwidth'),
                'theme_options'  => array('control_width' => 'col-md-3'),
                'placeholder'    => $this->translator->trans('badge_form_badge_selection', array(), 'badge'),
                'class'          => 'IcapBadgeBundle:Badge',
                'use_controller' => true,
                'property'       => sprintf("%sName", $locale),
                'repo_method'    => sprintf('findByName%sForAjax', ucfirst($locale))
            ));
    }

    public function getName()
    {
        return 'badge_claim_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\BadgeBundle\Entity\BadgeClaim',
                'translation_domain' => 'icap_badge'
            )
        );
    }
}
