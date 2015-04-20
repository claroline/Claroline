<?php

namespace HeVinci\FavouriteBundle\Installer;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '1.0', '>')) {
            $em = $this->container->get('entity.manager');
            $menuAction = $em
                ->getRepository('ClarolineCoreBundle:Resource\MenuAction')
                ->findBy(array('name' => 'hevinci_favourite'));

            $menuAction->setIsForm(true);
            $em->flush();
        }
    }
}