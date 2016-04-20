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
use Claroline\CoreBundle\Entity\Activity\ActivityRuleAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

/**
 * Resource types data fixture.
 */
class LoadResourceTypeData implements RequiredFixture
{
    /**
     * Loads one meta type (document) and four resource types handled by the platform core :
     * - File
     * - Directory
     * - Link
     * - Text
     * All these resource types have the 'document' meta type.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // resource type attributes : name, listable, navigable, class
        $resourceTypes = array(
            array('file', true),
            array('directory', true),
            array('text', true),
            array('resource_shortcut', false),
            array('activity', true),
        );

        $types[] = array();

        foreach ($resourceTypes as $attributes) {
            $type = new ResourceType();
            $type->setName($attributes[0]);
            $type->setExportable($attributes[1]);
            $manager->persist($type);
            $this->container->get('claroline.manager.mask_manager')->addDefaultPerms($type);
            $types[$attributes[0]] = $type;
        }

        //add special actions.
        $composeDecoder = new MaskDecoder();
        $composeDecoder->setValue(pow(2, 10));
        $composeDecoder->setName('compose');
        $composeDecoder->setResourceType($types['activity']);
        $manager->persist($composeDecoder);

        $activityMenu = new MenuAction();
        $activityMenu->setName('compose');
        $activityMenu->setAsync(false);
        $activityMenu->setIsCustom(true);
        $activityMenu->setValue(pow(2, 10));
        $activityMenu->setResourceType($types['activity']);
        $activityMenu->setIsForm(false);
        $manager->persist($activityMenu);

        $updateFile = new MenuAction();
        $updateFile->setName('update_file');
        $updateFile->setAsync(true);
        $updateFile->setIsCustom(true);
        $updateFile->setIsForm(false);
        $updateFile->setResourceType($types['file']);
        //16 is default for edit
        $updateFile->setValue(16);
        $manager->persist($updateFile);

        $fileAction = new ActivityRuleAction();
        $fileAction->setAction('resource-read');
        $fileAction->setResourceType($types['file']);
        $manager->persist($fileAction);

        $textAction = new ActivityRuleAction();
        $textAction->setAction('resource-read');
        $textAction->setResourceType($types['text']);
        $manager->persist($textAction);

        $badgeAwardAction = new ActivityRuleAction();
        $badgeAwardAction->setAction('badge-awarding');
        $manager->persist($badgeAwardAction);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
