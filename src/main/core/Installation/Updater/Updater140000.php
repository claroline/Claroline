<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Helper\RemovePluginTrait;
use Claroline\InstallationBundle\Updater\Updater;

class Updater140000 extends Updater
{
    use RemovePluginTrait;

    private ObjectManager $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function preUpdate(): void
    {
        $this->log('Remove IcapFormulaPlugin plugin...');
        $this->removePlugin('Icap', 'FormulaPluginBundle');
    }
}
