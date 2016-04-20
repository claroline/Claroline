<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\EntityRepository;
use Icap\BadgeBundle\Repository\BadgeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("icap_badge.form.badge.collection")
 */
class BadgeCollectionType extends AbstractType
{
    /** @var  \Icap\BadgeBundle\Repository\BadgeRepository */
    private $badgeRepository;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "badgeRepository" = @DI\Inject("icap_badge.repository.badge"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, PlatformConfigurationHandler $platformConfigHandler, TokenStorageInterface $tokenStorage)
    {
        $this->badgeRepository = $badgeRepository;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $builder
            ->add('name', 'text')
            ->add('userBadges', 'entity',
                array(
                     'class' => 'IcapBadgeBundle:UserBadge',
                     'query_builder' => function (EntityRepository $entityRepository) use ($user) {
                        return $entityRepository->createQueryBuilder('u')
                            ->where('u.user = :user')
                            ->setParameter('user', $user);
                     },
                     'empty_value' => '',
                     'property' => 'badge.name',
                     'multiple' => true,
                     'expanded' => true,
                )
            )
            ->add('is_shared', 'checkbox');
    }

    public function getName()
    {
        return 'badge_collection_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BadgeBundle\Entity\BadgeCollection',
                'translation_domain' => 'icap_badge',
                'csrf_protection' => false,
            )
        );
    }
}
