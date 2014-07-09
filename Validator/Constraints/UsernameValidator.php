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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @DI\Validator("username_validator")
 */
class UsernameValidator extends ConstraintValidator
{
    private $ch;

    /**
     * @DI\InjectParams({
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function setEntityManager(PlatformConfigurationHandler $ch)
    {
        $this->ch = $ch;
    }

    public function validate($value, Constraint $constraint)
    {
        $regex = $this->ch->getParameter('username_regex');

        if (!preg_match($regex, $value)) {
            $this->context->addViolation($constraint->error);
        }
    }
}
