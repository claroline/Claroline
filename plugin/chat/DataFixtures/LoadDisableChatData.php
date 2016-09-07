<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDisableChatData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $chatType = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->
            findOneByName('claroline_chat_room');

        $chatType->setIsEnabled(false);
        $manager->persist($chatType);
        $manager->flush();
    }
}
