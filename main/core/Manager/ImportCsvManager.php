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
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\AddRoleException;
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
    private $ut;

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
     *     "ut"               = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        GroupManager $groupManager,
        RoleManager $roleManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        ClaroUtilities $ut
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->ut = $ut;
    }

    public function parseCSVLines(array $lines)
    {
        $datas = [];
        $invalidSyntaxMsg = $this->translator->trans(
            'invalid_syntax',
            [],
            'platform'
        );

        foreach ($lines as $key => $line) {
            $lineNum = $key + 1;
            $lineDatas = str_getcsv($line, ';');
            $action = trim($lineDatas[count($lineDatas) - 1]);

            if (!isset($datas[$action])) {
                $datas[$action] = [];
            }
            $infos = [];
            $nbLineDatas = count($lineDatas);

            switch ($action) {
                case 'claro_create_user':

                    if ($nbLineDatas >= 6 && $nbLineDatas < 10) {
                        $infos[] = trim($lineDatas[0]);
                        $infos[] = trim($lineDatas[1]);
                        $infos[] = trim($lineDatas[2]);
                        $infos[] = trim($lineDatas[3]);
                        $infos[] = trim($lineDatas[4]);

                        if (7 === $nbLineDatas) {
                            $infos[] = trim($lineDatas[5]);
                        } elseif (8 === $nbLineDatas) {
                            $infos[] = trim($lineDatas[6]);
                        } elseif (9 === $nbLineDatas) {
                            $infos[] = trim($lineDatas[7]);
                        }
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_edit_user':
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

                        if (9 === $nbLineDatas) {
                            $infos[] = trim($lineDatas[7]);
                        }
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_delete_user':
                case 'claro_create_group':
                case 'claro_delete_group':
                case 'claro_empty_group':

                    if (2 === $nbLineDatas) {
                        $infos['name'] = trim($lineDatas[0]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_register_to_group':
                case 'claro_unregister_from_group':

                    if (3 === $nbLineDatas) {
                        $infos['username'] = trim($lineDatas[0]);
                        $infos['group_name'] = trim($lineDatas[1]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_create_workspace_role':
                case 'claro_delete_workspace_role':

                    if (3 === $nbLineDatas) {
                        $infos['ws_code'] = trim($lineDatas[0]);
                        $infos['role_name'] = trim($lineDatas[1]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                case 'claro_register_user_to_workspace':
                case 'claro_unregister_user_from_workspace':
                case 'claro_register_group_to_workspace':
                case 'claro_unregister_group_from_workspace':

                    if (4 === $nbLineDatas) {
                        $infos['name'] = trim($lineDatas[0]);
                        $infos['ws_code'] = trim($lineDatas[1]);
                        $infos['role_name'] = trim($lineDatas[2]);
                    } else {
                        $infos['error'] = "[$lineNum] $invalidSyntaxMsg";
                    }
                    break;

                default:
                    $infos['error'] = $this->translator->trans(
                        'invalid_action',
                        [],
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
        $logs = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $deletedTxt = $this->translator->trans(
            'has_been_deleted',
            [],
            'platform'
        );
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
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
        $logs = [];
        $identicalGroupTxt = $this->translator->trans(
            'identical_group_name',
            [],
            'platform'
        );
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $createdTxt = $this->translator->trans(
            'has_been_created',
            [],
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists',
            [],
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
                    $group->setGuid($this->ut->generateGuid());
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
        $logs = [];
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $emptiedTxt = $this->translator->trans(
            'has_been_emptied',
            [],
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
        $logs = [];
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $deletedTxt = $this->translator->trans(
            'has_been_deleted',
            [],
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
        $logs = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_group',
            [],
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
                    $this->groupManager->removeUsersFromGroup($group, [$user]);
                    $logs[] = "$userTxt [$username] $unregisteredTxt [$groupName]";
                }
            }
        }
        $this->om->endFlushSuite();

        return $logs;
    }

    public function manageGroupRegistration(array $datas)
    {
        $logs = [];
        $toRegister = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_to_group',
            [],
            'platform'
        );
        $cannotRegisteredTxt = $this->translator->trans(
            'users_cannot_be_registered_to_group',
            [],
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
                        $toRegister[$groupName] = [];
                        $toRegister[$groupName]['group'] = $group;
                        $toRegister[$groupName]['users'] = [];
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
        $logs = [];
        $identicalRoleTxt = $this->translator->trans(
            'identical_role_name',
            [],
            'platform'
        );
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $createdTxt = $this->translator->trans(
            'has_been_created_in_workspace',
            [],
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists_in_workspace',
            [],
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
                            'ROLE_WS_'.strtoupper($roleName).'_'.$workspace->getGuid(),
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
        $logs = [];
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            [],
            'platform'
        );
        $deletedTxt = $this->translator->trans(
            'has_been_deleted_in_workspace',
            [],
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
        $logs = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            [],
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_in_workspace',
            [],
            'platform'
        );
        $withRoleTxt = $this->translator->trans(
            'with_role',
            [],
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
        $logs = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            [],
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_role',
            [],
            'platform'
        );
        $inWorkspaceTxt = $this->translator->trans(
            'in_workspace',
            [],
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
        $logs = [];
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            [],
            'platform'
        );
        $registeredTxt = $this->translator->trans(
            'has_been_registered_in_workspace',
            [],
            'platform'
        );
        $withRoleTxt = $this->translator->trans(
            'with_role',
            [],
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
        $logs = [];
        $groupTxt = $this->translator->trans('group', [], 'platform');
        $workspaceTxt = $this->translator->trans('workspace', [], 'platform');
        $roleTxt = $this->translator->trans('role', [], 'platform');
        $nonExistentTxt = $this->translator->trans(
            'does_not_exist',
            [],
            'platform'
        );
        $nonExistentInWsTxt = $this->translator->trans(
            'does_not_exist_in_workspace',
            [],
            'platform'
        );
        $unregisteredTxt = $this->translator->trans(
            'has_been_unregistered_from_role',
            [],
            'platform'
        );
        $inWorkspaceTxt = $this->translator->trans(
            'in_workspace',
            [],
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
        $logs = [];
        $workspaces = [];
        $workspaceTxt = $this->translator->trans(
            'workspace',
            [],
            'platform'
        );
        $existedTxt = $this->translator->trans(
            'already_exists',
            [],
            'platform'
        );
        $creatingTxt = $this->translator->trans(
            'creating',
            [],
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
        $logs = [];
        $users = [];
        $userTxt = $this->translator->trans('user', [], 'platform');
        $usernameTxt = $this->translator->trans('username', [], 'platform');
        $mailTxt = $this->translator->trans('email', [], 'platform');
        $usedTxt = $this->translator->trans(
            'is_already_in_use',
            [],
            'platform'
        );
        $creatingTxt = $this->translator->trans(
            'creating',
            [],
            'platform'
        );

        foreach ($datas as $lineNb => $lineDatas) {
            if (isset($lineDatas['error'])) {
                $logs[] = $lineDatas['error'];
            } else {
                $username = $lineDatas[2];
                $email = $lineDatas[4];
                $code = $lineDatas[5];
                $user = $this->userManager
                    ->getUserByUsernameOrMailOrCode($username, $email, $code);

                if (is_null($user)) {
                    $users[] = $lineDatas;
                    $logs[] = "$userTxt [$username] : $creatingTxt...";
                } else {
                    $logs[] = "[$lineNb] $usernameTxt [$username] | $mailTxt [$email] $usedTxt";
                }
            }
        }

        if (count($users) > 0) {
            $this->userManager->importUsers($users, false);
        }

        return $logs;
    }
}
