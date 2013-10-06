<?php

namespace Claroline\CoreBundle\Library\Installation\Settings;

abstract class AbstractValidator
{
    const NOT_BLANK_EXPECTED = 'not_blank_expected';
    const NUMBER_EXPECTED = 'positive_number_expected';
    const INVALID_DRIVER = 'invalid_driver';

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
}
