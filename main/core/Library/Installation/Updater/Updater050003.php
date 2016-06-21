<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050003 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->moveDefaultTemplate();
        $this->removeOldTemplateDir();
        $this->moveThumbnailsDir();
        $this->addingResourceIcons();
        $this->addMyContactsTool();
    }

    public function moveThumbnailsDir()
    {
        $this->log('Moving thumbnail directory');
        $fs = new FileSystem();
        $oldIconDir = $this->container->getParameter('claroline.param.web_dir').'/thumbnails';

        try {
            $fs->rename($oldIconDir, $this->container->getParameter('claroline.param.thumbnails_directory'));
        } catch (\Exception $e) {
            $this->log('Operation already done...');
        }

        $om = $this->container->get('doctrine.orm.entity_manager');
        $iconRepository = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icons = $iconRepository->findCustomIcons();
        $i = 1;

        foreach ($icons as $icon) {
            if (strpos($icon->getRelativeUrl(), 'uploads/') === false) {
                $icon->setRelativeUrl('uploads/'.$icon->getRelativeUrl());
                $om->persist($icon);
                ++$i;
            }

            if ($i % 50 === 0) {
                $this->log('Flushing 50 icons...');
                $om->flush();
            }
        }

        $this->log('Final icon flush !');
        $om->flush();
    }

    private function removeOldTemplateDir()
    {
        $claroRoot = $this->container->getParameter('claroline.param.root_directory');
        $claroRoot .= '/templates';
        $this->log('Removing old template directory '.$claroRoot);
        $fs = new FileSystem();
        $fs->remove($claroRoot, true);
    }

    private function moveDefaultTemplate()
    {
        $this->log('Moving default template...');

        $fileDir = $this->container->getParameter('claroline.param.files_directory');
        $defaultTemplate = $this->container->getParameter('claroline.param.default_template');
        $newTemplateDir = $fileDir.'/templates';
        $newTemplate = $newTemplateDir.'/default.zip';

        $fs = new Filesystem();
        $fs->mkdir($newTemplateDir);
        $fs->copy($defaultTemplate, $newTemplate);
    }

    private function addingResourceIcons()
    {
        $coreIconWebDirRelativePath = 'bundles/clarolinecore/images/resources/icons/';
        $om = $this->container->get('doctrine.orm.entity_manager');
        $repo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $resourceImages = $this->container->get('claroline.manager.icon_manager')->getDefaultIconMap();

        foreach ($resourceImages as $resourceImage) {
            $imgs = $repo->findBy(array('mimeType' => $resourceImage[1]));

            if (count($imgs) === 0) {
                $this->log('Adding icon for mime type '.$resourceImage[1].'...');
                $rimg = new ResourceIcon();
                $rimg->setRelativeUrl($coreIconWebDirRelativePath.$resourceImage[0]);
                $rimg->setMimeType($resourceImage[1]);
                $rimg->setShortcut(false);
                $om->persist($rimg);

                $this->container->get('claroline.manager.icon_manager')
                    ->createShortcutIcon($rimg);
            }
        }
    }

    private function addMyContactsTool()
    {
        $this->log('Installing my_contacts tool...');
        $om = $this->container->get('claroline.persistence.object_manager');
        $toolManager = $this->container->get('claroline.manager.tool_manager');
        $myContactsTool = $toolManager->getOneToolByName('my_contacts');

        if (is_null($myContactsTool)) {
            $myContactsTool = new Tool();
            $myContactsTool->setName('my_contacts');
            $myContactsTool->setClass('users');
            $myContactsTool->setIsWorkspaceRequired(false);
            $myContactsTool->setIsDesktopRequired(false);
            $myContactsTool->setDisplayableInWorkspace(false);
            $myContactsTool->setDisplayableInDesktop(true);
            $myContactsTool->setExportable(false);
            $myContactsTool->setIsConfigurableInWorkspace(false);
            $myContactsTool->setIsConfigurableInDesktop(false);
            $myContactsTool->setIsLockedForAdmin(false);
            $myContactsTool->setIsAnonymousExcluded(true);
            $toolManager->create($myContactsTool);

            $myContactsOt = new OrderedTool();
            $myContactsOt->setTool($myContactsTool);
            $myContactsOt->setType(0);
            $myContactsOt->setOrder(10);
            $myContactsOt->setLocked(false);
            $myContactsOt->setName($myContactsTool->getName());
            $myContactsOt->setVisibleInDesktop(true);
            $om->persist($myContactsOt);
            $om->flush();
        }
    }
}
