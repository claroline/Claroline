<?php

namespace Icap\NotificationBundle\Tests\API;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class NotificationControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

        /**
         * @Route("/notifications.{_format}", name="icap_notifications", defaults={"_format":"json"})
         * @View(serializerGroups={"api_notification"})
         * @EXT\ParamConverter("user", converter="current_user")
         */
        public function testGetNotificationsAction()
        {
        }

       /**
        * Mark all notifications as read.
        *
        * @Route("/notifications/read.{_format}", name="icap_notifications_read", defaults={"_format":"json"})
        * @View(serializerGroups={"api_notification"})
        * @EXT\ParamConverter("user", converter="current_user")
        */
       public function testGetNotificationsReadAction()
       {
       }
}
