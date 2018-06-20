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
use Claroline\ForumBundle\Entity\Validation\User as ValidationUser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.forum_manager")
 */
class Manager
{
    private $finder;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder"),
     *     "om"     = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(FinderProvider $finder, ObjectManager $om)
    {
        $this->finder = $finder;
        $this->om = $om;
    }

    public function getHotSubjects(Forum $forum)
    {
        $date = new \DateTime();
        $date->modify('-1 week');

        $messages = $this->finder->fetch(
          'Claroline\ForumBundle\Entity\Message',
          ['createdAfter' => $date, 'forum' => $forum->getUuid()]
        );

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

    public function getValidationUser(User $creator, Forum $forum, $autoFlush = false)
    {
        $user = $this->om->getRepository('ClarolineForumBundle:Validation\User')->findOneBy([
          'user' => $creator,
          'forum' => $forum,
        ]);

        if (!$user) {
            $user = new ValidationUser();
            $user->setForum($forum);
            $user->setUser($creator);
            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }
}
