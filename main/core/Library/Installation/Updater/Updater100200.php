<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/12/17
 * Time: 3:30 PM.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater100200 extends Updater
{
    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $inwicastPlugin = $this->om->getRepository('ClarolineCoreBundle:Plugin')->findOneBy([
            'vendorName' => 'Inwicast', 'bundleName' => 'ClarolinePluginBundle',
        ]);
        if ($inwicastPlugin) {
            $this->log('Changing Inwicast plugin namespace...');
            $inwicastPlugin->setVendorName('Icap');
            $inwicastPlugin->setBundleName('InwicastBundle');
            $this->om->persist($inwicastPlugin);
            $this->om->flush();
        }
    }
}
