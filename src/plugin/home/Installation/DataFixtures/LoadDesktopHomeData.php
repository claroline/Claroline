<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Installation\DataFixtures;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Entity\Type\WidgetsTab;
use Claroline\InstallationBundle\Fixtures\PostInstallInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create a default tab in desktop Home and
 * add a widget to display the list of workspaces of the current user.
 */
class LoadDesktopHomeData extends AbstractFixture implements PostInstallInterface
{
    private TranslatorInterface $translator;
    private SerializerProvider $serializer;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->translator = $container->get('translator');
        $this->serializer = $container->get(SerializerProvider::class);
    }

    public function load(ObjectManager $manager): void
    {
        $existingTabs = $manager->getRepository(HomeTab::class)->findBy([
            'contextName' => DesktopContext::getName(),
        ]);

        if (empty($existingTabs)) {
            $defaultTab = [
                'title' => $this->translator->trans('information', [], 'platform'),
                'longTitle' => $this->translator->trans('information', [], 'platform'),
                'slug' => 'information',
                'type' => WidgetsTab::getType(),
                'class' => WidgetsTab::class,
                'position' => 1,
                'restrictions' => [
                    'hidden' => false,
                ],
                'parameters' => [
                    'widgets' => [[
                        'name' => $this->translator->trans('my_workspaces', [], 'workspace'),
                        'visible' => true,
                        'display' => [
                            'layout' => [1],
                            'color' => '#333333',
                            'backgroundType' => 'color',
                            'background' => '#ffffff',
                        ],
                        'parameters' => [],
                        'contents' => [[
                            'type' => 'list',
                            'source' => 'my_workspaces',
                            'parameters' => [
                                'display' => 'tiles-sm',
                                'enableDisplays' => false,
                                'availableDisplays' => [],
                                'card' => [
                                    'display' => [
                                        'icon',
                                        'flags',
                                        'subtitle',
                                        'description',
                                        'footer',
                                    ],
                                ],
                                'paginated' => true,
                                'count' => true,
                            ],
                        ]],
                    ]],
                ],
            ];

            $tab = new HomeTab();
            $tab->setContextName(DesktopContext::getName());

            $this->serializer->deserialize($defaultTab, $tab);

            $manager->persist($tab);
            $manager->flush();
        }
    }
}
