<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\BadgeBundle\Repository\BadgeRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.form.claimBadge")
 * @DI\Tag("form.type")
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
            ->add('badge', EntityType::class, [
                'attr' => ['class' => 'fullwidth'],
                'attr' => ['control_width' => 'col-md-3'],
                'placeholder' => $this->translator->trans('badge_form_badge_selection', [], 'icap_badge'),
                'class' => 'IcapBadgeBundle:Badge',
                'choice_label' => function ($badge) use ($locale) {
                    return $badge->getTranslationForLocale($locale)->getName();
                },
                'query_builder' => function (BadgeRepository $er) use ($user, $locale) {
                    $qb = $er->createQueryBuilder('b');

                    $expr = $qb->expr()->andX(
                        $qb->expr()->eq('t.locale', ':locale'),
                        $qb->expr()->isNull('b.workspace')
                      );

                    $inExpr = $qb->expr()->in(
                        'b.workspace',
                        'SELECT w FROM ClarolineCoreBundle:Workspace\Workspace w
                            JOIN w.roles r
                            JOIN r.users u
                            WHERE u.id = '.$user->getId()
                    );

                    return
                      $qb->join('b.translations', 't')
                        ->andWhere($qb->expr()->orX(
                          $expr,
                          $inExpr
                      ))
                      ->setParameter('locale', $locale);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeClaim',
                'translation_domain' => 'icap_badge',
            ]
        );
    }
}
