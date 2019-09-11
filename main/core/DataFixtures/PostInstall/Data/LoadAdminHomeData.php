<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\PostInstall\Data;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Symfony\Component\Translation\TranslatorInterface;

class LoadAdminHomeData implements RequiredFixture
{
    private $container;
    /** @var TranslatorInterface */
    private $translator;
    /** @var SerializerProvider */
    private $serializer;

    public function setContainer($container)
    {
        $this->container = $container;

        $this->translator = $container->get('translator');
        $this->serializer = $container->get('claroline.api.serializer');
    }

    public function load(ObjectManager $manager)
    {
        $defaultTab = [
            'title' => $this->translator->trans('informations', [], 'platform'),
            'longTitle' => $this->translator->trans('informations', [], 'platform'),
            'slug' => 'informations',
            'type' => HomeTab::TYPE_ADMIN,
            'position' => 1,
            'restrictions' => [
                'hidden' => false,
            ],
            'widgets' => [[
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
                    'source' => 'admin_tools',
                    'parameters' => [
                        'showResourceHeader' => false,
                        'display' => 'tiles-sm',
                        'enableDisplays' => false,
                        'availableDisplays' => [],
                        'card' => [
                            'display' => ['icon', 'flags', 'subtitle'],
                        ],
                        'paginated' => false,
                        'count' => false,
                    ],
                ]],
            ]],
        ];

        $tab = $this->serializer->deserialize($defaultTab, new HomeTab());

        $manager->persist($tab);
        $manager->flush();
    }
}
