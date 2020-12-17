<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;

class LogWorkspaceRegistrationDeclineEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'role-subscribe-decline';
    protected $details;
    protected $role;
    protected $user;
    protected $workspace;

    /**
     * Constructor.
     */
    public function __construct(WorkspaceRegistrationQueue $queue)
    {
        $this->user = $queue->getUser();
        $this->workspace = $queue->getWorkspace();
        $this->role = $queue->getRole();

        $details = array('role' => array('name' => $this->role->getTranslationKey()));
        $details['workspace'] = array(
            'name' => $this->workspace->getName(),
            'id' => $this->workspace->getId(),
        );
        $details['receiverUser'] = array(
            'firstName' => $this->user->getFirstName(),
            'lastName' => $this->user->getLastName(),
        );
        $this->details = $details;

        parent::__construct(
            self::ACTION,
            $this->details,
            $this->user,
            null,
            null,
            $this->role,
            $this->workspace
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
    }

    public function getActionKey()
    {
        return $this::ACTION;
    }

    public function getExcludeUserIds()
    {
        return array();
    }

    public function getIconKey()
    {
        return;
    }

    public function getIncludeUserIds()
    {
        return array($this->user->getId());
    }

    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, array());

        return $notificationDetails;
    }

    public function getSendToFollowers()
    {
        return false;
    }

    public function isAllowedToNotify()
    {
        return true;
    }
}
