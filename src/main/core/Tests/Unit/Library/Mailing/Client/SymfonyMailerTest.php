<?php

namespace Claroline\CoreBundle\Tests\Unit\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Mailing\Client\SymfonyMailer;
use Claroline\CoreBundle\Library\Mailing\Message;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\Mailer\MailerInterface;

class SymfonyMailerTest extends MockeryTestCase
{
    public function testSend()
    {
        $mailer = $this->mock(MailerInterface::class);
        $mailer->shouldReceive('send')->once();

        $symfonyMailer = new SymfonyMailer($mailer);

        $message = new Message();
        $message->subject('Subject');
        $message->from('from@claroline.com');
        $message->replyTo('reply_to@claroline.com');
        $message->body('<p>Hello</p>');
        $message->bcc(['bcc1@claroline.com', 'bcc2@claroline.com']);
        $message->to('to@claroline.com');

        $this->assertSame('Subject', $message->getAttribute('subject'));
        $this->assertSame('from@claroline.com', $message->getAttribute('from'));
        $this->assertSame('reply_to@claroline.com', $message->getAttribute('reply_to'));
        $this->assertSame('<p>Hello</p>', $message->getAttribute('body'));
        $this->assertSame('bcc1@claroline.com', $message->getAttribute('bcc')[0]);
        $this->assertSame('bcc2@claroline.com', $message->getAttribute('bcc')[1]);
        $this->assertCount(2, $message->getAttribute('bcc'));
        $this->assertSame('to@claroline.com', $message->getAttribute('to')[0]);
        $this->assertTrue($symfonyMailer->send($message));
    }
}
