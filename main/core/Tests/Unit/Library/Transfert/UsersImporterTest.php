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
        $this->importer = new UsersImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $usernames, $emails, $codes, $isExceptionThrown, $manifest)
    {
        $roles = Yaml::parse(file_get_contents($manifest));
        $this->importer->setConfiguration($roles);

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
            //valid
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => false,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //username is already in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(array('username' => 'user1')),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //email is already in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(array('mail' => 'mail1@gmail.com')),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //code is already in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/users01.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(array('code' => 'USER01')),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //username found twice in the configuration
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_username.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //email found twice in the configuration
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_email.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //code found twice in the configuration
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/existing_code.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //role not found in the configuration
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/unknown_role.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //owner is included in the user list
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/owner_in_user_list.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
            ),
            //email is invalid
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/users/invalid_email.yml',
                'usernames' => array(),
                'emails' => array(),
                'codes' => array(),
                'isExceptionThrow' => true,
                'manifest' => __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml',
            ),
        );
    }
}
