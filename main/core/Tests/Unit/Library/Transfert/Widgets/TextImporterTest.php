<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Widgets;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Widgets\TextImporter;
use Symfony\Component\Yaml\Yaml;

class TextImporterTest extends MockeryTestCase
{
    private $importer;

    public function __construct()
    {
        parent::__construct();

        $this->importer = new TextImporter();
    }

    public function testValidate()
    {
        $configPath = __DIR__.'/../../../../Stub/transfert/valid/full/tools/widgets/text01.yml';

        $data = Yaml::parse(file_get_contents($configPath));
        $this->importer->validate($data);
    }
}
