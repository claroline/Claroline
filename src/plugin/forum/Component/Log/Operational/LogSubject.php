<?php

namespace Claroline\ForumBundle\Component\Log\Operational;

use Claroline\ForumBundle\Entity\Subject;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogSubject extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'forum_subject';
    }

    protected static function getEntityClass(): string
    {
        return Subject::class;
    }

    /**
     * @param Subject $object
     */
    protected function getObjectName(mixed $object): string
    {
        return $object->getTitle();
    }
}
