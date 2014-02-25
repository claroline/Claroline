<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.importer.merger")
 * @todo: testme
 */
class Resolver
{
    public function mergeUserConfigurations($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $users['users'] = array();

        if (isset($data['members']['users'])) {
            $users['users'] = $data['members']['users'];
        }

        if (isset($data['userfiles'])) {
            foreach ($data['userfiles'] as $userpath) {
                $filepath = $path . $ds . $userpath['path'];
                $userdata = Yaml::parse(file_get_contents($filepath));
                foreach ($userdata as $udata) {
                    foreach ($udata as $user) {
                        $users['users'][] = array('user' => $user['user']);
                    }
                }
            }
        }

        return $users;
    }

    public function mergeGroupConfigurations($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $groups['groups'] = array();

        if (isset($data['members']['groups'])) {
            $groups['groups'] = $data['members']['groups'];
        }

        if (isset($data['groupfiles'])) {
            foreach ($data['groupfiles'] as $grouppath) {
                $filepath = $path . $ds . $grouppath['path'];
                $groupdata = Yaml::parse(file_get_contents($filepath));
                foreach ($groupdata as $gdata) {
                    foreach ($gdata as $group) {
                        $groups['groups'][] = array('group' => $group['group']);
                    }
                }
            }
        }

        return $groups;
    }

    public function mergeRoleConfigurations($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $roles['roles'] = array();

        if (isset($data['roles'])) {
            $roles['roles'] = $data['roles'];
        }

        if (isset($data['rolefiles'])) {
            foreach ($data['rolefiles'] as $rolepath) {
                $filepath = $path . $ds . $rolepath['path'];
                $roledata = Yaml::parse(file_get_contents($filepath));
                foreach ($roledata as $rdata) {
                    foreach ($rdata as $role) {
                        $roles['roles'][] = array('role' => $role['role']);
                    }
                }
            }
        }

        return $roles;
    }

    public function mergeToolConfigurations($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $tools['tools'] = array();

        if (isset($data['tools'])) {
            $tools['tools'] = $data['tools'];
        }

        if (isset($data['toolfiles'])) {
            foreach ($data['toolfiles'] as $toolpath) {
                $filepath = $path . $ds . $toolpath['path'];
                $tooldata = Yaml::parse(file_get_contents($filepath));
                foreach ($tooldata as $tdata) {
                    foreach ($tdata as $tool) {
                        $tools['tools'][] = array('tool' => $tool['tool']);
                    }
                }
            }
        }

        return $tools;
    }
} 