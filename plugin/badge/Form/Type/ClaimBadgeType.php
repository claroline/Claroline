<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(TranslatorInterface $translator, PlatformConfigurationHandler $platformConfigHandler, TokenStorageInterface $tokenStorage)
    {
        $this->translator = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $locale = (null === $user->getLocale()) ? $this->platformConfigHandler->getParameter('locale_language') : $user->getLocale();

        $builder
            ->add('badge', 'zenstruck_ajax_entity', array(
                'attr' => array('class' => 'fullwidth'),
                'theme_options' => array('control_width' => 'col-md-3'),
                'placeholder' => $this->translator->trans('badge_form_badge_selection', array(), 'icap_badge'),
                'class' => 'IcapBadgeBundle:Badge',
                'use_controller' => true,
                'repo_method' => sprintf('findByNameForAjax'),
                'extra_data' => array('userId' => $user->getId(), 'locale' => $locale),
            ));
    }

    public function getName()
    {
        return 'badge_claim_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeClaim',
                'translation_domain' => 'icap_badge',
            )
        );
    }
}
