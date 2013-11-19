<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\DataFixtures;

use Claroline\ForumBundle\Entity\ForumOptions;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOptionsData extends AbstractFixture
{
     /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $options = new ForumOptions();
        $options->setMessages(30);
        $options->setSubjects(30);
        $manager->persist($options);
        $manager->flush();
    }
}
