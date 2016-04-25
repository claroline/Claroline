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

class Setting
{
    private $description;
    private $descriptionParameters;
    private $isCorrect;
    private $isRequired;

    /**
     * @param string $description
     * @param array  $descriptionParameters
     * @param bool   $isCorrect
     * @param bool   $isRequired
     */
    public function __construct($description, array $descriptionParameters, $isCorrect, $isRequired)
    {
        $this->description = $description;
        $this->descriptionParameters = $descriptionParameters;
        $this->isCorrect = $isCorrect;
        $this->isRequired = $isRequired;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getDescriptionParameters()
    {
        return $this->descriptionParameters;
    }

    /**
     * @return string
     */
    public function getRawDescription()
    {
        $description = $this->description;

        foreach ($this->descriptionParameters as $name => $parameter) {
            $description = str_replace("%{$name}%", $parameter, $description);
        }

        return $description;
    }

    /**
     * @return bool
     */
    public function isCorrect()
    {
        return $this->isCorrect;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }
}
