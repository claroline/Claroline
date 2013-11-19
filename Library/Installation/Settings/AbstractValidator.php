<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Settings;

abstract class AbstractValidator
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

    protected $errors = array();
    private $hadValidationCall = false;

    public function validate()
    {
        $this->errors = array();
        $this->doValidate();
        $this->hadValidationCall = true;

        return $this->errors;
    }

    public function isValid()
    {
        return $this->hadValidationCall && count($this->errors) === 0;
    }

    abstract protected function doValidate();

    protected function checkIsNotBlank($property, $value)
    {
        if (empty($value)) {
            $this->errors[$property] = static::NOT_BLANK_EXPECTED;

            return false;
        }

        return true;
    }

    protected function checkIsPositiveNumber($property, $value)
    {
        if (!is_numeric($value) || (int) $value < 0) {
            $this->errors[$property] = static::NUMBER_EXPECTED;

            return false;
        }

        return true;
    }

    protected function checkIsValidDriver($property, $value)
    {
        if (!in_array($value, array('pdo_mysql', 'pdo_pgsql'))) {
            $this->errors[$property] = static::INVALID_DRIVER;

            return false;
        }

        return true;
    }

    protected function checkIsValidEmail($property, $value)
    {
        if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value)) {
            $this->errors[$property] = static::INVALID_EMAIL;

            return false;
        }

        return true;
    }

    protected function checkIsValidUrl($property, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$property] = static::INVALID_URL;

            return false;
        }

        return true;
    }

    protected function checkIsNotUnderMinLength($property, $value, $minLength)
    {
        if (strlen($value) < $minLength) {
            $this->errors[$property] = static::UNDER_MIN_LENGTH;

            return false;
        }

        return true;
    }

    protected function checkIsNotOverMaxLength($property, $value, $maxLength)
    {
        if (strlen($value) > $maxLength) {
            $this->errors[$property] = static::OVER_MAX_LENGTH;

            return false;
        }

        return true;
    }

    protected function checkIsValidMailTransport($property, $value)
    {
        if (!in_array($value, array('smtp', 'sendmail', 'gmail'))) {
            $this->errors[$property] = static::INVALID_TRANSPORT;

            return false;
        }

        return true;
    }

    protected function checkIsValidMailEncryption($property, $value)
    {
        if (!in_array($value, array('', 'ssl', 'tls'))) {
            $this->errors[$property] = static::INVALID_ENCRYPTION;

            return false;
        }

        return true;
    }

    protected function checkIsValidMailAuthMode($property, $value)
    {
        if (!in_array($value, array('', 'plain', 'login', 'cram-md5'))) {
            $this->errors[$property] = static::INVALID_AUTH_MODE;

            return false;
        }

        return true;
    }
}
