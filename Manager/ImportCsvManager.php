<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Manager\Exception\AddRoleException;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.import_csv_manager")
 */
class ImportCsvManager
{
    private $om;
    private $translator;
    private $groupManager;
    private $roleManager;
    private $userManager;
    private $workspaceManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     * })
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        GroupManager $groupManager,
        RoleManager $roleManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    )
    {
        $this->om = $om;
        $this->translator = $translator;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    public function parseCSVLines(array $lines)
    {
        $datas = array();
        $invalidSyntaxMsg = $this->translator->trans(
            'invalid_syntax',
            array(),
            'platform'
        );

        foreach ($lines as $key => $line) {
            $lineNum = $key + 1;
            $lineDatas = str_getcsv($line, ';');
            $action = trim($lineDatas[count($lineDatas) - 1]);

            if (!isset($datas[$action])) {
                $datas[$action] = array();
            }
            $infos = array();
            $nbLineDatas = count($lineDatas);

            switch ($action) {

                case 'claro_create_user':

                    if ($nbLineDatas >= 6 && $nbLineDatas < 10) {
                        $infos[] = trim($lineDatas[0]);
                        $infos[] = trim($lineDatas[1]);
                        $infos[] = trim($lineDatas[2]);
                        $infos[] = trim($lineDatas[3]);
                        $infos[] = trim($lineDatas[4]);

                        if ($nbLineDatas === 7) {
                            $infos[] = trim($lineDatas[5]);
                        } elseif ($nbLineDatas === 8)  {
                            $infos[] = trim($lineDatas[6]);
                        } elseif ($nbLineDatas === 9) {
                            $infos[] = trim($lineDatas[7]);
                        }
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_edit_user' :
                    break;

                case 'claro_create_workspace':

                    if ($nbLineDatas >= 8 && $nbLineDatas < 10) {
                        $infos[] = trim($lineDatas[0]);
                        $infos[] = trim($lineDatas[1]);
                        $infos[] = trim($lineDatas[2]);
                        $infos[] = trim($lineDatas[3]);
                        $infos[] = trim($lineDatas[4]);
                        $infos[] = trim($lineDatas[5]);
                        $infos[] = trim($lineDatas[6]);

                        if ($nbLineDatas === 9) {
                            $infos[] = trim($lineDatas[7]);
                        }
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_delete_user' :
                case 'claro_create_group' :
                case 'claro_delete_group' :
                case 'claro_empty_group' :

                    if ($nbLineDatas === 2) {
                        $infos['name'] = trim($lineDatas[0]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_register_to_group' :
                case 'claro_unregister_from_group' :

                    if ($nbLineDatas === 3) {
                        $infos['username'] = trim($lineDatas[0]);
                        $infos['group_name'] = trim($lineDatas[1]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_create_workspace_role' :
                case 'claro_delete_workspace_role' :

                    if ($nbLineDatas === 3) {
                        $infos['ws_code'] = trim($lineDatas[0]);
                        $infos['role_name'] = trim($lineDatas[1]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_register_user_to_workspace' :
                case 'claro_unregister_user_from_workspace' :
                case 'claro_register_group_to_workspace' :
                case 'claro_unregister_group_from_workspace' :

                    if ($nbLineDatas === 4) {
                        $infos['name'] = trim($lineDatas[0]);
                        $infos['ws_code'] = trim($lineDatas[1]);
                        $infos['role_name'] = trim($lineDatas[2]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                default :
                    $infos['error'] = $this->translator->trans(
                        'invalid_action',
                        array(),
                        'platform'
                    );
                    break;
            }
            $datas[$action][$lineNum] = $infos;
        }

        return $datas;
    }

    public function manageUserDeletion(array $datas)
    {
        $logs = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $deletedTxt = $this->translator->trans(
            'has_been_deleted',
            array(),
            'platform'
        );
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $error = $lineDatas['error'];
                $logs[] = "[$lineNb] $error";
            } else {
                $username = $lineDatas['name'];
                $user = $this->userManager->getOneUserByUsername($username);

                if (!is_null($user)) {
                    $this->userManager->deleteUser($user);
                    $logs[] = "$userTxt [$username] $deletedTxt";
                } else {
                    $logs[] = "[$lineNb] $userTxt [$username] $nonExistentTxt";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupCreation(array $datas)
    {
        $logs = array();
        $identicalGroupTxt = $this->translator->trans(
            'identical_group_name',
            array(),
            'platform'
        );
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $createdTxt = $this->translator->trans(
            'has_been_created',
            array(),
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        // Checks for double entries
        foreach ($datas as $lineNb => $lineDatas) {

            if (!isset($lineDatas['error'])) {
                $groupName = strtolower($lineDatas['name']);

                foreach ($datas as $lineNbBis => $lineDatasBis) {

                    if ($lineNb !== $lineNbBis &&
                        !isset($lineDatasBis['error']) &&
                        $groupName === strtolower($lineDatasBis['name'])) {

                        $groupNameBis = $lineDatasBis['name'];
                        $datas[$lineNb]['error'] =
                            "[$lineNb] $identicalGroupTxt [$groupName]";
                        $datas[$lineNbBis]['error'] =
                            "[$lineNbBis] $identicalGroupTxt [$groupNameBis]";
                    }
                }
            }
        }

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $groupName = $lineDatas['name'];
                $group = $this->groupManager->getGroupByName($groupName);

                if (is_null($group)) {
                    $group = new Group();
                    $group->setName($groupName);
                    $this->om->persist($group);
                    $logs[] = "$groupTxt [$groupName] $createdTxt";
                } else {
                    $logs[] = "$groupTxt [$groupName] $existedTxt";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupEmptying(array $datas)
    {
        $logs = array();
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $emptiedTxt = $this->translator->trans(
            'has_been_emptied',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $groupName = $lineDatas['name'];
                $group = $this->groupManager->getGroupByName($groupName);

                if (is_null($group)) {
                    $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                } else {
                    $this->groupManager->removeAllUsersFromGroup($group);
                    $logs[] = "$groupTxt [$groupName] $emptiedTxt";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupDeletion(array $datas)
    {
        $logs = array();
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $deletedTxt = $this->translator->trans(
            'has_been_deleted',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $groupName = $datas[$lineNb]['name'];
                $group = $this->groupManager->getGroupByName($groupName);

                if (is_null($group)) {
                    $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                } else {
                    $this->om->remove($group);
                    $logs[] = "$groupTxt [$groupName] $deletedTxt";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupUnregistration(array $datas)
    {
        $logs = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_group',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $username = $lineDatas['username'];
                $groupName = $lineDatas['group_name'];
                $user = $this->userManager->getOneUserByUsername($username);
                $group = $this->groupManager->getGroupByName($groupName);

                if (is_null($user) || is_null($group)) {

                    if (is_null($user)) {
                        $logs[] = "[$lineNb] $userTxt [$username] $nonExistentTxt";
                    }

                    if (is_null($group)) {
                        $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                    }
                } else {
                    $username = $user->getUsername();
                    $groupName = $group->getName();
                    $this->groupManager->removeUsersFromGroup($group, array($user));
                    $logs[] = "$userTxt [$username] $unregisteredTxt [$groupName]";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupRegistration(array $datas)
    {
        $logs = array();
        $toRegister = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_to_group',
            array(),
            'platform'
        );
        $cannotRegisteredTxt = $this->translator->trans(
            'users_cannot_be_registered_to_group',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $username = $lineDatas['username'];
                $groupName = $lineDatas['group_name'];
                $user = $this->userManager->getOneUserByUsername($username);
                $group = $this->groupManager->getGroupByName($groupName);

                if (is_null($user) || is_null($group)) {

                    if (is_null($user)) {
                        $logs[] = "[$lineNb] $userTxt [$username] $nonExistentTxt";
                    }

                    if (is_null($group)) {
                        $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                    }
                } else {
                    $groupName = $group->getName();

                    if (!isset($toRegister[$groupName])) {
                        $toRegister[$groupName] = array();
                        $toRegister[$groupName]['group'] = $group;
                        $toRegister[$groupName]['users'] = array();
                    }
                    $toRegister[$groupName]['users'][] = $user;
                }
            }
        }

        foreach ($toRegister as $groupName => $registerDatas) {
            $group = $registerDatas['group'];
            $users = $registerDatas['users'];

            try {
                $this->groupManager->addUsersToGroup($group, $users);

                foreach ($users as $user) {
                    $username = $user->getUsername();
                    $logs[] = "$userTxt [$username] $registeredTxt [$groupName]";
                }
            } catch (AddRoleException $e) {
                $logs[] = "$cannotRegisteredTxt [$groupName]";
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceRoleCreation(array $datas)
    {
        $logs = array();
        $identicalRoleTxt = $this->translator->trans(
            'identical_role_name',
            array(),
            'platform'
        );
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $createdTxt = $this->translator->trans(
            'has_been_created_in_workspace',
            array(),
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists_in_workspace',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        // Checks for double entries
        foreach ($datas as $lineNb => $lineDatas) {

            if (!isset($lineDatas['error'])) {
                $wsCode = strtolower($lineDatas['ws_code']);
                $roleName = strtolower($lineDatas['role_name']);

                foreach ($datas as $lineNbBis => $lineDatasBis) {

                    if ($lineNb !== $lineNbBis &&
                        !isset($lineDatasBis['error']) &&
                        $wsCode === strtolower($lineDatasBis['ws_code']) &&
                        $roleName === strtolower($lineDatasBis['role_name'])) {

                        $roleNameBis = $lineDatasBis['role_name'];
                        $datas[$lineNb]['error'] =
                            "[$lineNb] $identicalRoleTxt [$roleName]";
                        $datas[$lineNbBis]['error'] =
                            "[$lineNbBis] $identicalRoleTxt [$roleNameBis]";
                    }
                }
            }
        }

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $wsCode = $lineDatas['ws_code'];
                $roleName = $lineDatas['role_name'];
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($workspace)) {
                    $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                } else {
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );

                    if (is_null($role)) {
                        $this->roleManager->createWorkspaceRole(
                            'ROLE_WS_' . strtoupper($roleName) . '_' . $workspace->getGuid(),
                            $roleName,
                            $workspace
                        );
                        $logs[] = "$roleTxt [$roleName] $createdTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $logs[] = "$roleTxt [$roleName] $existedTxt [$workspaceName ($workspaceCode)]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceRoleDeletion(array $datas)
    {
        $logs = array();
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            array(),
            'platform'
        );
        $deletedTxt = $this->translator->trans(
            'has_been_deleted_in_workspace',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $wsCode = $lineDatas['ws_code'];
                $roleName = $lineDatas['role_name'];
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($workspace)) {
                    $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                } else {
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );

                    if (is_null($role)) {
                        $logs[] = "$roleTxt [$roleName] $nonExistentInWsTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $this->om->remove($role);
                        $key = $role->getTranslationKey();
                        $logs[] = "$roleTxt [$key] $deletedTxt [$workspaceName ($workspaceCode)]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceRegistration(array $datas)
    {
        $logs = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            array(),
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_in_workspace',
            array(),
            'platform'
        );
        $withRoleTxt = $this->translator->trans(
            'with_role',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $username = $lineDatas['name'];
                $wsCode = $lineDatas['ws_code'];
                $roleName = $lineDatas['role_name'];
                $user = $this->userManager->getOneUserByUsername($username);
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($user) || is_null($workspace)) {

                    if (is_null($user)) {
                        $logs[] = "[$lineNb] $userTxt [$username] $nonExistentTxt";
                    }

                    if (is_null($workspace)) {
                        $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                    }
                } else {
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();

                    if (is_null($role)) {
                        $logs[] = "[$lineNb] $roleTxt [$roleName] $nonExistentInWsTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $this->roleManager->associateRole($user, $role);
                        $key = $role->getTranslationKey();
                        $logs[] = "$userTxt [$username] $registeredTxt [$workspaceName ($workspaceCode)] $withRoleTxt [$key]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceUnregistration(array $datas)
    {
        $logs = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            array(),
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_role',
            array(),
            'platform'
        );
        $inWorkspaceTxt = $this->translator->trans(
            'in_workspace',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs = $lineDatas['error'];
            } else {
                $username = $lineDatas['name'];
                $wsCode = $lineDatas['workspace_code'];
                $roleName = $lineDatas['role_name'];
                $user = $this->userManager->getOneUserByUsername($username);
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($user) || is_null($workspace)) {

                    if (is_null($user)) {
                        $logs[] = "[$lineNb] $userTxt [$username] $nonExistentTxt";
                    }

                    if (is_null($workspace)) {
                        $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                    }
                } else {
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();

                    if (is_null($role)) {
                        $logs[] = "[$lineNb] $roleTxt [$roleName] $nonExistentInWsTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $this->roleManager->associateRole($user, $role);
                        $key = $role->getTranslationKey();
                        $logs[] = "$userTxt [$username] $unregisteredTxt [$key] $inWorkspaceTxt [$workspaceName ($workspaceCode)]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceGroupRegistration(array $datas)
    {
        $logs = array();
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            array(),
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_in_workspace',
            array(),
            'platform'
        );
        $withRoleTxt = $this->translator->trans(
            'with_role',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $groupName = $lineDatas['name'];
                $wsCode = $lineDatas['ws_code'];
                $roleName = $lineDatas['role_name'];
                $group = $this->groupManager->getGroupByName($groupName);
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($group) || is_null($workspace)) {

                    if (is_null($group)) {
                        $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                    }

                    if (is_null($workspace)) {
                        $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                    }
                } else {
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );
                    $groupName = $group->getName();
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();

                    if (is_null($role)) {
                        $logs[] = "[$lineNb] $roleTxt [$roleName] $nonExistentInWsTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $this->roleManager->associateRole($group, $role);
                        $key = $role->getTranslationKey();
                        $logs[] = "$groupTxt [$groupName] $registeredTxt [$workspaceName ($workspaceCode)] $withRoleTxt [$key]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceGroupUnregistration(array $datas)
    {
        $logs = array();
        $groupTxt = $this->translator->trans('group', array(), 'platform');
        $workspaceTxt = $this->translator->trans('workspace', array(), 'platform');
        $roleTxt = $this->translator->trans('role', array(), 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            array(),
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            array(),
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_role',
            array(),
            'platform'
        );
        $inWorkspaceTxt = $this->translator->trans(
            'in_workspace',
            array(),
            'platform'
        );

        $this->om->startFlushSuite();

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $groupName = $lineDatas['name'];
                $wsCode = $lineDatas['ws_code'];
                $roleName = $lineDatas['role_name'];
                $group = $this->groupManager->getGroupByName($groupName);
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($group) || is_null($workspace)) {

                    if (is_null($group)) {
                        $logs[] = "[$lineNb] $groupTxt [$groupName] $nonExistentTxt";
                    }

                    if (is_null($workspace)) {
                        $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $nonExistentTxt";
                    }
                } else {
                    $role = $this->roleManager->getWorkspaceRoleByNameOrTranslationKey(
                        $workspace,
                        $roleName
                    );
                    $groupName = $group->getName();
                    $workspaceName = $workspace->getName();
                    $workspaceCode = $workspace->getCode();

                    if (is_null($role)) {
                        $logs[] = "[$lineNb] $roleTxt [$roleName] $nonExistentInWsTxt [$workspaceName ($workspaceCode)]";
                    } else {
                        $this->roleManager->associateRole($group, $role);
                        $key = $role->getTranslationKey();
                        $logs[] = "$groupTxt [$groupName] $unregisteredTxt [$key] $inWorkspaceTxt [$workspaceName ($workspaceCode)]";
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageWorkspaceCreation(array $datas)
    {
        $logs = array();
        $workspaces = array();
        $workspaceTxt = $this->translator->trans(
            'workspace',
            array(),
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists',
            array(),
            'platform'
        );
        $creatingTxt = $this->translator->trans(
            'creating',
            array(),
            'platform'
        );

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $wsCode = $lineDatas[1];
                $workspace = $this->workspaceManager->getWorkspaceByCode($wsCode);

                if (is_null($workspace)) {
                    $workspaces[] = $lineDatas;
                    $logs[] = "$workspaceTxt [$wsCode] : $creatingTxt...";
                } else {
                    $logs[] = "[$lineNb] $workspaceTxt [$wsCode] $existedTxt";
                }
            }
        }

        if (count($workspaces) > 0) {
            $this->workspaceManager->importWorkspaces($workspaces);
        }

        return $logs;
    }

    public function manageUserCreation(array $datas)
    {
        $logs = array();
        $users = array();
        $userTxt = $this->translator->trans('user', array(), 'platform');
        $usernameTxt = $this->translator->trans('username', array(), 'platform');
        $mailTxt = $this->translator->trans('mail', array(), 'platform');
        $usedTxt = $this->translator->trans(
            'is_already_in_use',
            array(),
            'platform'
        );
        $creatingTxt = $this->translator->trans(
            'creating',
            array(),
            'platform'
        );

        foreach ($datas as $lineNb => $lineDatas) {

            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $username = $lineDatas[2];
                $mail = $lineDatas[4];
                $user = $this->userManager
                    ->getUserByUsernameOrMail($username, $mail);

                if (is_null($user)) {
                    $users[] = $lineDatas;
                    $logs[] = "$userTxt [$username] : $creatingTxt...";
                } else {
                    $logs[] = "[$lineNb] $usernameTxt [$username] | $mailTxt [$mail] $usedTxt";
                }
            }
        }

        if (count($users) > 0) {
            $this->userManager->importUsers($users, false);
        }

        return $logs;
    }
}
