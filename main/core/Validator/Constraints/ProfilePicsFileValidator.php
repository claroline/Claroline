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
 * @DI\Validator("profile_pics_file_validator")
 */
class ProfilePicsFileValidator extends ConstraintValidator
{
    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("claroline.manager.user_manager"),
     *     "translator"  = @DI\Inject("translator"),
     * })
     */
    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator
    ) {
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value !== null) {
            $archive = new \ZipArchive();
            if (true === $archive->open($value->getPathName())) {
                for ($i = 0; $i < $archive->numFiles; ++$i) {
                    $file = $archive->getNameIndex($i);
                    $fileName = basename($file);
                    $username = preg_replace("/\.[^.]+$/", '', $fileName);
                    $user = $this->userManager->getUserByUsername($username);
                    if (!$user) {
                        $msg = $this->translator->trans(
                            'username_doesnt_exist',
                            array('%username%' => $username),
                            'validators'
                        ).' ';
                        $this->context->addViolation($msg);
                    }
                    //check if the name exists and so on
                }
                //set the default properties of the workspace here if we can find them.
            } else {
                $msg = $this->translator->trans('corrupted_archive', array(), 'platform');
                $this->context->addViolation($msg);
            }
        }
    }
}
