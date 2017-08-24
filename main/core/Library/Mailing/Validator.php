<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Mailing;

class Validator
{
    const NOT_BLANK_EXPECTED = 'not_blank_expected';
    const NUMBER_EXPECTED = 'positive_number_expected';
    const INVALID_DRIVER = 'invalid_driver';
    const INVALID_EMAIL = 'invalid_email';
    const INVALID_URL = 'invalid_url';
    const OVER_MAX_LENGTH = 'over_max_length';
    const UNDER_MIN_LENGTH = 'under_min_length';
    const INVALID_TRANSPORT = 'invalid_transport';
    const INVALID_ENCRYPTION = 'invalid_encryption';
    const INVALID_AUTH_MODE = 'invalid_auth_mode';

    public function checkIsPositiveNumber($value)
    {
        if (!is_numeric($value) || (int) $value < 0) {
            return static::NUMBER_EXPECTED;
        }
    }

    public function checkIsNotBlank($value)
    {
        if (empty($value)) {
            return static::NOT_BLANK_EXPECTED;
        }
    }

    public function checkIsValidEmail($value)
    {
        if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value)) {
            return static::INVALID_EMAIL;
        }
    }

    public function checkIsValidUrl($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return static::INVALID_URL;
        }
    }

    public function checkIsValidMailTransport($value)
    {
        if (!in_array($value, ['smtp', 'sendmail', 'gmail', 'postal'])) {
            return static::INVALID_TRANSPORT;
        }

        return true;
    }

    public function checkIsValidMailEncryption($value)
    {
        if (!in_array($value, ['', 'ssl', 'tls'])) {
            return static::INVALID_ENCRYPTION;
        }
    }

    public function checkIsValidMailAuthMode($value)
    {
        if (!in_array($value, ['', 'plain', 'login', 'cram-md5'])) {
            return static::INVALID_AUTH_MODE;
        }
    }
}
