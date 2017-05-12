<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @DI\Validator("csv_workspace_user_import_by_full_name_validator")
 */
class CsvWorkspaceUserImportByFullNameValidator extends ConstraintValidator
{
    private $roleManager;
    private $translator;
    private $userManager;
    private $validator;
    private $ut;

    /**
     * @DI\InjectParams({
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "trans"       = @DI\Inject("translator"),
     *     "userManager" = @DI\Inject("claroline.manager.user_manager"),
     *     "validator"   = @DI\Inject("validator"),
     *     "ut"          = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        ValidatorInterface $validator,
        ClaroUtilities $ut
    ) {
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->ut = $ut;
    }

    public function validate($value, Constraint $constraint)
    {
        $usernameErrors = [];
        $roleNameErrors = [];
        $workspace = $constraint->getDefaultOption();
        $wsRoleNames = [];
        $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);

        foreach ($workspaceRoles as $workspaceRole) {
            $wsRoleNames[] = $workspaceRole->getTranslationKey();
        }

        $data = $this->ut->formatCsvOutput(file_get_contents($value));
        $lines = str_getcsv($data, PHP_EOL);

        foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if (trim($line) !== '' && $nbElements < 2) {
                $this->context->addViolation($constraint->message);

                return;
            }
        }

        foreach ($lines as $i => $line) {
            if (trim($line) !== '') {
                $datas = explode(';', $line);
                $username = $datas[0];
                $roleName = $datas[1];
                $firstName = isset($datas[2]) ? $datas[2] : null;
                $lastName = isset($datas[3]) ? $datas[3] : null;

                $user = null;
                if (!empty($username)) {
                    $user = $this->userManager->getOneUserByUsername($username);
                } elseif (!empty($firstName) && !empty($lastName)) {
                    $user = $this->userManager->getUsersByFirstNameAndLastName($firstName, $lastName);
                }

                if (is_null($user)) {
                    $msg = $this->translator->trans(
                        'workspace_user_invalid',
                        ['%username%' => $username, '%line%' => $i + 1],
                        'platform'
                    ).' ';
                    $usernameErrors[] = $msg;
                } elseif (is_array($user) && empty($user)) {
                    $msg = $this->translator->trans(
                            'workspace_user_invalid',
                            ['%username%' => $firstName.' '.$lastName, '%line%' => $i + 1],
                            'platform'
                        ).' ';
                    $usernameErrors[] = $msg;
                } elseif (is_array($user) && count($user) > 1) {
                    $msg = $this->translator->trans(
                            'workspace_user_not_unique',
                            ['%name%' => $firstName.' '.$lastName, '%line%' => $i + 1],
                            'platform'
                        ).' ';
                    $usernameErrors[] = $msg;
                }

                if (!in_array($roleName, $wsRoleNames)) {
                    $msg = $this->translator->trans(
                        'line_number',
                        ['%line%' => $i + 1],
                        'platform'
                    ).' ';
                    $msg .= $this->translator->trans(
                        'unavailable_role',
                        ['%translationKey%' => $roleName],
                        'platform'
                    );
                    $roleNameErrors[] = $msg;
                }
            }
        }

        foreach ($usernameErrors as $error) {
            $this->context->addViolation($error);
        }

        foreach ($roleNameErrors as $error) {
            $this->context->addViolation($error);
        }
    }
}
