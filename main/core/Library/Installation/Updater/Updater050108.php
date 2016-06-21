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

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050108 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateTranslations();
    }

    public function updateTranslations()
    {
        $this->log('Updating locales...');
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $locales = array('en', 'es', 'fr');
        $ch->setParameter('locales', $locales);
    }
}
