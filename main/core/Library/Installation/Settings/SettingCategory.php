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

class SettingCategory
{
    private $name;
    private $settings = array();
    private $hasFailedRequirement = false;
    private $hasFailedRecommendation = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addRequirement($description, array $descriptionParameters, $isCorrect)
    {
        $this->doAddSetting($description, $descriptionParameters, $isCorrect, true);
    }

    public function addRecommendation($description, array $descriptionParameters, $isCorrect)
    {
        $this->doAddSetting($description, $descriptionParameters, $isCorrect, false);
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function hasIncorrectSetting()
    {
        return $this->hasFailedRequirement || $this->hasFailedRecommendation;
    }

    public function hasFailedRequirement()
    {
        return $this->hasFailedRequirement;
    }

    public function hasFailedRecommendation()
    {
        return $this->hasFailedRecommendation;
    }

    public function getIncorrectSettings()
    {
        $settings = array();

        foreach ($this->settings as $setting) {
            if (!$setting->isCorrect()) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    private function doAddSetting($description, array $descriptionParameters, $isCorrect, $isRequired)
    {
        if (!$isCorrect) {
            if ($isRequired) {
                $this->hasFailedRequirement = true;
            } else {
                $this->hasFailedRecommendation = true;
            }
        }

        $this->settings[] = new Setting($description, $descriptionParameters, $isCorrect, $isRequired);
    }
}
