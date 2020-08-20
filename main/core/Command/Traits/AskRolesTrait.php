<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Traits;

use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Removes users from the platform.
 */
trait AskRolesTrait
{
    public function askRoles($all, $input, $output, ObjectManager $om, $helper)
    {
        $roles = $om
            ->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();
        $roleNames = array_map(function ($role) {
            return $role->getName();
        }, $roles);
        $roleNames[] = 'NONE';

        $questionString = $all ? 'Roles to exclude: ' : 'Roles to include: ';
        $question = new ChoiceQuestion($questionString, $roleNames);
        $question->setMultiselect(true);
        $roleNames = $helper->ask($input, $output, $question);

        return array_filter($roles, function ($role) use ($roleNames) {
            return in_array($role->getName(), $roleNames);
        });
    }
}
