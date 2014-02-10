<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Badge;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.badge.collection")
 */
class BadgeCollectionType extends AbstractType
{
    /** @var  \Claroline\CoreBundle\Repository\Badge\BadgeRepository */
    private $badgeRepository;

    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "badgeRepository"       = @DI\Inject("claroline.repository.badge"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->badgeRepository       = $badgeRepository;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Claroline\CoreBundle\Entity\Badge\Badge[] $badgeChoices */
        $badgeChoices  = $this->badgeRepository->findOrderedByName($this->platformConfigHandler->getParameter('locale_language'));

        foreach ($badgeChoices as $badgeChoice) {
            $badgeChoice->setLocale($this->platformConfigHandler->getParameter('locale_language'));
        }

        $builder
            ->add('name', 'text')
            ->add('badges', 'entity',
                array(
                     'class'       => 'ClarolineCoreBundle:Badge\Badge',
                     'choices'     => $badgeChoices,
                     'empty_value' => '',
                     'property'    => 'name',
                     'multiple'    => true,
                     'expanded'    => true
                )
            );
    }

    public function getName()
    {
        return 'badge_collection_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\Badge\BadgeCollection',
                'translation_domain' => 'badge',
                'csrf_protection'    => false
            )
        );
    }
}
