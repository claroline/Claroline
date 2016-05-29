<?php

namespace Claroline\MessageBundle\Tests\API;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class MessageControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    /**
     * @Route("/received.{_format}", name="claro_received_message", defaults={"_format":"json"})
     * @View(serializerGroups={"api_message"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function testGetReceivedAction()
    {
    }

     /**
      * @Route("/sent.{_format}", name="claro_sent_message", defaults={"_format":"json"})
      * @View(serializerGroups={"api_message"})
      * @EXT\ParamConverter("user", converter="current_user")
      */
     public function testGetSentAction()
     {
     }

     /**
      * @Route("/removed.{_format}", name="claro_removed_message", defaults={"_format":"json"})
      * @View(serializerGroups={"api_message"})
      * @EXT\ParamConverter("user", converter="current_user")
      */
     public function testGetRemovedAction()
     {
     }
}
