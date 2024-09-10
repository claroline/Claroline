<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;

class ForumManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function getValidationUser(User $creator, Forum $forum)
    {
        $user = $this->om->getRepository(UserValidation::class)->findOneBy([
          'user' => $creator,
          'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($creator);
            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }
}
