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

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040804 extends Updater
{
    private $container;

    /** @var \Claroline\CoreBundle\Persistence\ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $installedBundles = $this->container->getParameter('kernel.bundles');

        if (isset($installedBundles['IcapPortfolioBundle'])) {
            $icapPortfolioPlugin = $this->om->getRepository('ClarolineCoreBundle:Plugin')->findOneByBundleFQCN($installedBundles['IcapPortfolioBundle']);

            if (null === $icapPortfolioPlugin) {
                $this->log('    Creation of Portfolio plugin in database.');

                $icapPortfolioPlugin = new Plugin();
                $icapPortfolioPlugin->setVendorName('Icap');
                $icapPortfolioPlugin->setBundleName('PortfolioBundle');
                $icapPortfolioPlugin->setHasOptions(false);
                $this->om->persist($icapPortfolioPlugin);
                $this->om->flush();
            }
        }
    }
}
