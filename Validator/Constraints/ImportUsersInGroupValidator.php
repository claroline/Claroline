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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Validator("import_user_in_group_validator")
 */
class ImportUsersInGroupValidator extends ConstraintValidator
{
    private $validator;
    private $translator;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("claroline.manager.user_manager"),
     *     "translator"  = @DI\Inject("translator"),
     * })
     */
    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator
    )
    {
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        $usernames = str_getcsv(file_get_contents($value), PHP_EOL);

        foreach ($usernames as $username) {

            if ($this->userManager->getUserByUsername(trim($username)) === null) {
                $msg = $this->translator->trans(
                    "username_doesnt_exist",
                    array('%username%' => $username),
                    'validators'
                ) . ' ';

                $this->context->addViolation($msg);
            }
        }
    }
} 