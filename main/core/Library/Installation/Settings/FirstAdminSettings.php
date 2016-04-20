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

class FirstAdminSettings extends AbstractValidator
{
    private $firstName;
    private $lastName;
    private $username;
    private $password;
    private $email;

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param array $settings
     */
    public function bindData(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (method_exists($this, $method = 'set'.ucfirst($name))) {
                $this->{$method}($value);
            }
        }
    }

    protected function doValidate()
    {
        $this->checkIsNotBlank('firstName', $this->firstName)
            && $this->checkIsNotOverMaxLength('firstName', $this->firstName, 50);
        $this->checkIsNotBlank('lastName', $this->lastName)
            && $this->checkIsNotOverMaxLength('lastName', $this->lastName, 50);
        $this->checkIsNotBlank('username', $this->username)
            && $this->checkIsNotUnderMinLength('username', $this->username, 3)
            && $this->checkIsNotOverMaxLength('username', $this->username, 255);
        $this->checkIsNotBlank('password', $this->password)
            && $this->checkIsNotUnderMinLength('password', $this->password, 4)
            && $this->checkIsNotOverMaxLength('password', $this->password, 255);
        $this->checkIsNotBlank('email', $this->email)
            && $this->checkIsNotOverMaxLength('email', $this->email, 255)
            && $this->checkIsValidEmail('email', $this->email);
    }
}
