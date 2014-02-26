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
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\UsersImporter;
use Symfony\Component\Yaml\Yaml;

class UsersImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->merger = $this->mock('Claroline\CoreBundle\Library\Transfert\Merger');
        $this->importer = new UsersImporter($this->om, $this->merger);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $usernames, $emails, $codes, $isExceptionThrown)
    {
        if ($isExceptionThrown) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $repo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\User')->andReturn($repo);

        $repo->shouldReceive('findUsernames')->andReturn($usernames);
        $repo->shouldReceive('findEmails')->andReturn($emails);
        $repo->shouldReceive('findCodes')->andReturn($codes);

        $data = Yaml::parse(file_get_contents($path));
        $users['users'] = $data['users'];
        $this->importer->validate($users);
    }

    public function validateProvider()
    {
        return array(
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => false
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(array('username' => 'user1')),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(array('mail' => 'mail1@gmail.com')),
                'codes' => array(),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(array('code' => 'USER01')),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_username.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_email.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_code.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/unknown_role.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true
            )
        );
    }
} 