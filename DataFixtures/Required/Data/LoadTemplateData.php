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

use Claroline\CoreBundle\Entity\Workspace\Template;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

/**
 * Resource types data fixture.
 */
class LoadTemplateData implements RequiredFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $templatesArray = array(
            array('hash' => 'default.zip', 'name' => 'default')
        );

        foreach ($templatesArray as $templateItem) {
            $template = new Template();
            $template->setName($templateItem['name']);
            $template->setHash($templateItem['hash']);
            $manager->persist($template);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
