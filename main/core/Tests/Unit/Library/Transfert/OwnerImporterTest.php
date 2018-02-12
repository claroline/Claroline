<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\OwnerImporter;
use Symfony\Component\Yaml\Yaml;

class OwnerImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->importer = new OwnerImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $usernames, $emails, $codes, $isExceptionThrown)
    {
        $manifest = Yaml::parse(file_get_contents($path));
        $this->importer->setConfiguration($manifest);

        if ($isExceptionThrown) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $repo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\User')->andReturn($repo);
        $repo->shouldReceive('findUsernames')->andReturn($usernames);
        $repo->shouldReceive('findEmails')->andReturn($emails);
        $repo->shouldReceive('findCodes')->andReturn($codes);
        $data = Yaml::parse(file_get_contents($path));
        $owner['owner'] = $data['members']['owner'];
        $this->importer->validate($owner);
    }

    public function validateProvider()
    {
        return [
            //valid
            [
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
                'usernames' => [],
                'emails' => [],
                'codes' => [],
                'isExceptionThrow' => false,
            ],
            //username exists
            [
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
                'usernames' => [['username' => 'ezs']],
                'emails' => [],
                'codes' => [],
                'isExceptionThrow' => true,
            ],
            //email exists
            [
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
                'usernames' => [],
                'emails' => [['email' => 'owner@owner.com']],
                'codes' => [],
                'isExceptionThrow' => true,
            ],
            //code exists
            [
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
                'usernames' => [],
                'emails' => [],
                'codes' => [['code' => 'OWNER']],
                'isExceptionThrow' => true,
            ],
            //code exists
            [
                'path' => __DIR__.'/../../../Stub/transfert/invalid/invalid_owner_mail.yml',
                'usernames' => [],
                'emails' => [],
                'codes' => [],
                'isExceptionThrow' => true,
            ],
        ];
    }
}
