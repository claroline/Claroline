<?php

namespace HeVinci\FavouriteBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '1.1', '<')) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $menuAction = $em
                ->getRepository('ClarolineCoreBundle:Resource\MenuAction')
                ->findOneBy(['name' => 'hevinci_favourite']);

            $menuAction->setIsForm(true);
            $em->flush();
        }
    }

    public function postUninstall()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $menuAction = $em
            ->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findOneBy(['name' => 'hevinci_favourite']);

        $em->remove($menuAction);
        $em->flush();
    }
}
