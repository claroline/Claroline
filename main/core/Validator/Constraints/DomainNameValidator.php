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

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("domain_name_validator")
 */
class DomainNameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        //null is perfectly valid: the system will find it itself.
        if ('' === $value || null === $value) {
            return;
        }

        $protocols = ['http', 'https'];
        $urls = array_map(
            function ($protocol) use ($value) {
                return $protocol.'://'.$value;
            },
            $protocols
        );

        $found = false;

        foreach ($urls as $url) {
            if ($this->urlExists($url)) {
                $found = true;
            }
        }

        if (!$found) {
            $this->context->addViolation($constraint->message);
        }
    }

    public function urlExists($url)
    {
        $found = false;
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if (200 === $httpCode) {
            $found = true;
        }

        curl_close($handle);

        return $found;
    }
}
