<?php

namespace Icap\BadgeBundle\Form\Type;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\EntityRepository;
use Icap\BadgeBundle\Repository\BadgeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service("icap_badge.form.badge.collection")
 */
class BadgeCollectionType extends AbstractType
{
    /** @var  \Icap\BadgeBundle\Repository\BadgeRepository */
    private $badgeRepository;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var SecurityContext */
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "badgeRepository"       = @DI\Inject("icap_badge.repository.badge"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "securityContext"       = @DI\Inject("security.context")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, PlatformConfigurationHandler $platformConfigHandler, SecurityContext $securityContext)
    {
        $this->badgeRepository       = $badgeRepository;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->securityContext       = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->securityContext->getToken()->getUser();

        $builder
            ->add('name', 'text')
            ->add('userBadges', 'entity',
                array(
                     'class'       => 'IcapBadgeBundle:UserBadge',
                     'query_builder' => function(EntityRepository $entityRepository) use($user) {
                        return $entityRepository->createQueryBuilder('u')
                            ->findByUser($user);
                     },
                     'empty_value' => '',
                     'property'    => 'name',
                     'multiple'    => true,
                     'expanded'    => true
                )
            )
            ->add('is_shared', 'checkbox');
    }

    public function getName()
    {
        return 'badge_collection_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\BadgeBundle\Entity\BadgeCollection',
                'translation_domain' => 'icap_badge',
                'csrf_protection'    => false
            )
        );
    }
}
