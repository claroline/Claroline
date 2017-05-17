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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @DI\Validator("csv_user_validator")
 */
class CsvUserValidator extends ConstraintValidator
{
    private $om;
    private $translator;
    private $userManager;
    private $validator;
    private $groupManager;
    private $roleManager;
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "trans"                  = @DI\Inject("translator"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "validator"              = @DI\Inject("validator"),
     *     "ut"                     = @DI\Inject("claroline.utilities.misc"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        AuthenticationManager $authenticationManager,
        ObjectManager $om,
        TranslatorInterface $translator,
        UserManager $userManager,
        ValidatorInterface $validator,
        ClaroUtilities $ut,
        GroupManager $groupManager,
        RoleManager $roleManager,
        PlatformConfigurationHandler $platformConfigHandler
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->om = $om;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->ut = $ut;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    public function validate($value, Constraint $constraint)
    {
        $mode = $constraint->getDefaultOption();
        $data = $this->ut->formatCsvOutput(file_get_contents($value));
        $lines = str_getcsv($data, PHP_EOL);
        $authDrivers = $this->authenticationManager->getDrivers();
        $newUserCount = 0;
        $isUserAdminCodeUnique = $this->platformConfigHandler->getParameter('is_user_admin_code_unique');

        foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if (trim($line) !== '') {
                if ($nbElements < 5) {
                    $this->context->addViolation($constraint->message);

                    return;
                }
            }
        }

        $usernames = [];
        $mails = [];
        $codes = [];

        if ($mode === 1) {
            $currentDate = new \DateTime();
            $timestamp = $currentDate->getTimestamp();
            $fakeUsername = '@@@fake_username_'.$timestamp.'@@@';
            $fakeMail = 'fake_email_'.
                $timestamp.
                '@fake-'.
                $timestamp.
                '-claroline-connect.com';
        }

        foreach ($lines as $i => $line) {
            if (trim($line) !== '') {
                $user = explode(';', $line);
                $firstName = $user[0];
                $lastName = $user[1];
                $username = $user[2];
                $pwd = $user[3];
                $email = trim($user[4]);

                if (isset($user[5])) {
                    $code = trim($user[5]) === '' ? null : $user[5];
                } else {
                    $code = null;
                }

                if (isset($user[6])) {
                    $phone = trim($user[6]) === '' ? null : $user[6];
                } else {
                    $phone = null;
                }

                if (isset($user[7])) {
                    $authentication = trim($user[7]) === '' ? null : $user[7];
                } else {
                    $authentication = null;
                }

                if (isset($user[8])) {
                    $modelName = trim($user[8]) === '' ? null : $user[8];
                } else {
                    $modelName = null;
                }

                if (isset($user[10])) {
                    $organizationName = trim($user[10]) === '' ? null : $user[10];
                } else {
                    $organizationName = null;
                }

                (!array_key_exists($email, $mails)) ?
                    $mails[$email] = [$i + 1] :
                    $mails[$email][] = $i + 1;
                (!array_key_exists($username, $usernames)) ?
                    $usernames[$username] = [$i + 1] :
                    $usernames[$username][] = $i + 1;
                if (!empty($code) && $isUserAdminCodeUnique) {
                    (!array_key_exists($code, $codes)) ?
                        $codes[$code] = [$i + 1] :
                        $codes[$code][] = $i + 1;
                }

                $existingUser = null;

                if ($mode === 1) {
                    try {
                        $existingUser = $this->userManager->getUserByUsernameOrMailOrCode(
                            $username,
                            $email,
                            $code
                        );
                    } catch (NonUniqueResultException $e) {
                        $msg = $this->translator->trans(
                            'line_number',
                            ['%line%' => $i + 1],
                            'platform'
                        );
                        $msg .= ' '.$this->translator->trans(
                            'username_and_email_from_two_different_users',
                            [
                                '%username%' => $username,
                                '%email%' => $email,
                            ],
                            'platform'
                        );
                        $this->context->addViolation($msg);
                        continue;
                    }
                }

                if (!is_null($existingUser)) {
                    // For an update, we will validate user with a fake username and email
                    $upperExistingUsername = strtoupper(trim($existingUser->getUsername()));
                    $upperExistingMail = strtoupper(trim($existingUser->getMail()));
                    $upperUsername = strtoupper(trim($username));
                    $upperMail = strtoupper(trim($email));

                    if ($upperExistingUsername === $upperUsername &&
                        $upperExistingMail === $upperMail) {
                        $existingUser->setUsername($fakeUsername);
                        $existingUser->setMail($fakeMail);
                    } elseif ($upperExistingUsername === $upperUsername) {
                        $existingUser->setUsername($fakeUsername);
                        $existingUser->setMail($email);
                    } else {
                        $existingUser->setUsername($username);
                        $existingUser->setMail($fakeMail);
                    }

                    $existingUser->setFirstName($firstName);
                    $existingUser->setLastName($lastName);

                    if (!empty($pwd)) {
                        $existingUser->setPlainPassword($pwd);
                    }
                    $existingUser->setAdministrativeCode($code);
                    $existingUser->setPhone($phone);
                    $errors = $this->validator->validate(
                        $existingUser,
                        ['registration', 'Default']
                    );
                    $existingUser->setUsername($username);
                    $existingUser->setMail($email);
                } else {
                    ++$newUserCount;
                    $newUser = new User();
                    $newUser->setFirstName($firstName);
                    $newUser->setLastName($lastName);
                    $newUser->setUsername($username);
                    $newUser->setPlainPassword($pwd);
                    $newUser->setMail($email);
                    $newUser->setAdministrativeCode($code);
                    $newUser->setPhone($phone);
                    $errors = $this->validator->validate($newUser, ['registration', 'Default']);
                }

                if ($authentication) {
                    if (!in_array($authentication, $authDrivers)) {
                        $msg = $this->translator->trans(
                            'authentication_invalid',
                            ['%authentication%' => $authentication, '%line%' => $i + 1],
                            'platform'
                        ).' ';

                        $this->context->addViolation($msg);
                    }
                }

                foreach ($errors as $error) {
                    $this->context->addViolation(
                        $this->translator->trans('line_number', ['%line%' => $i + 1], 'platform').' '.
                        $error->getInvalidValue().' : '.$error->getMessage()
                    );
                }
            }

            if ($modelName) {
                $model = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($modelName);

                if (!$model) {
                    $msg = $this->translator->trans(
                        'workspace_invalid',
                        ['%model%' => $modelName, '%line%' => $i + 1],
                        'platform'
                    ).' ';
                    $this->context->addViolation($msg);
                }
            }

            if ($organizationName) {
                $organization = $this->om
                    ->getRepository('Claroline\CoreBundle\Entity\Organization\Organization')
                    ->findOneByName($organizationName);

                if (!$organization) {
                    $msg = $this->translator->trans(
                        'organization_invalid',
                        ['%organization%' => $organizationName, '%line%' => $i + 1],
                        'platform'
                    ).' ';
                    $this->context->addViolation($msg);
                }
            }
        }

        foreach ($usernames as $username => $lines) {
            if (count($lines) > 1) {
                $msg = $this->translator->trans(
                    'username_found_at',
                    ['%username%' => $username, '%lines%' => $this->getLines($lines)],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }
        }

        foreach ($mails as $mail => $lines) {
            if (count($lines) > 1) {
                $msg = $this->translator->trans(
                    'email_found_at',
                    ['%email%' => $mail, '%lines%' => $this->getLines($lines)],
                    'platform'
                ).' ';
                $this->context->addViolation($msg);
            }
        }

        if ($isUserAdminCodeUnique) {
            foreach ($codes as $code => $lines) {
                if (count($lines) > 1) {
                    $msg = $this->translator->trans(
                            'code_found_at',
                            ['%code%' => $code, '%lines%' => $this->getLines($lines)],
                            'platform'
                        ).' ';
                    $this->context->addViolation($msg);
                }
            }
        }

        $role = $this->roleManager->getRoleByName('ROLE_USER');

        $totalUsers = $this->roleManager->countUsersByRoleIncludingGroup($role);
        $maxUsers = $role->getMaxUsers();

        if ($maxUsers < $totalUsers + $newUserCount) {
            $msg = $this->translator->trans(
                'user_limit_reached',
                [],
                'platform'
            ).' ';
            $this->context->addViolation($msg);
        }
    }

    private function getLines($lines)
    {
        $countLines = count($lines);
        $l = '';

        foreach ($lines as $i => $line) {
            $l .= $line;

            if ($i < $countLines - 1) {
                $l .= ', ';
            }
        }

        return $l;
    }
}
