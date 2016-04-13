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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadTemplateData implements RequiredFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory').'/default.zip';
        @unlink($destinationPath);
        copy($this->container->getParameter('claroline.param.default_template'), $destinationPath);
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory').'/personal.zip';
        @unlink($destinationPath);
        copy($this->container->getParameter('claroline.param.personal_template'), $destinationPath);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
