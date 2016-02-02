<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Persistence\ObjectManager;

class LoadThemeData implements RequiredFixture
{
    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $themes = $this->container->get('claroline.manager.theme_manager')
            ->listStockThemeNames();

        foreach ($themes as $name) {
            $theme = new Theme();
            $theme->setName($name);
            $manager->persist($theme);
        }
    }

}
