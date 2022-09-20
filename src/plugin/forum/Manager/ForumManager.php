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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;

class ForumManager
{
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        FinderProvider $finder,
        ObjectManager $om
    ) {
        $this->finder = $finder;
        $this->om = $om;
    }

    public function getHotSubjects(Forum $forum)
    {
        $date = new \DateTime();
        $date->modify('-1 week');

        /** @var Message[] $messages */
        $messages = $this->finder->fetch(Message::class, [
            'createdAfter' => $date,
            'forum' => $forum->getUuid(),
        ]);

        $totalMessages = count($messages);

        if (0 === $totalMessages) {
            return [];
        }

        $subjects = [];

        foreach ($messages as $message) {
            $total = isset($subjects[$message->getSubject()->getUuid()]) ?
              $subjects[$message->getSubject()->getUuid()] + 1 : 1;

            $subjects[$message->getSubject()->getUuid()] = $total;
        }

        $totalSubjects = count($subjects);
        $avg = $totalMessages / $totalSubjects;

        foreach ($subjects as $subject => $count) {
            if ($count < $avg) {
                unset($subjects[$subject]);
            }
        }

        return array_keys($subjects);
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
