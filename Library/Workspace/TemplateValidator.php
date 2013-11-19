<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Workspace;

class TemplateValidator
{
    public function validate($config)
    {
        $errors = array();

        $expectedKeys = array(
            'tools',
            'roles',
            'creator_role',
            'tools_permissions',
            'name'
        );

        foreach ($expectedKeys as $key) {
            if (!isset($config[$key])) {
                $errors[] = "The entry '{$key}' is missing";
            }
        }

        if (count($errors) === 0) {
            return true;
        } else {
            return $errors;
        }
    }
}
