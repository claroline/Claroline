<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Validator\Constraints;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("xmpp_host_validator")
 */
class XmppHostValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $options = new Options($value);
        $options->setUsername($adminUsername)->setPassword($adminPassword);
        $client = new Client($options);
        $client->connect();
    }
}
