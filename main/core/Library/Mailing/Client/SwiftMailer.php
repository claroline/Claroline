<?php

namespace Claroline\CoreBundle\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Mailing\Message;
use Claroline\CoreBundle\Library\Mailing\Validator;
use JMS\DiExtraBundle\Annotation as DI;
use Swift_Attachment;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_TransportException;

/**
 * @DI\Service("claroline.mailing.swiftmailer")
 * @DI\Tag("claroline.mailing")
 */
class SwiftMailer implements MailClientInterface
{
    const UNABLE_TO_START_TRANSPORT = 'unable_to_start_transport';
    const UNABLE_TO_START_SMTP = 'unable_to_start_smtp';
    const UNABLE_TO_START_SENDMAIL = 'unable_to_start_sendmail';
    const UNABLE_TO_START_GMAIL = 'unable_to_start_gmail';

    /**
     * @DI\InjectParams({
     *     "ch"     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "mailer" = @DI\Inject("mailer")
     * })
     */
    public function __construct(\Swift_Mailer $mailer, PlatformConfigurationHandler $ch)
    {
        $this->mailer = $mailer;
        $this->ch = $ch;
    }

    public function getTransports()
    {
        return['gmail', 'smtp', 'sendmail'];
    }

    public function test(array $data)
    {
        $validator = new Validator();
        $errors = [];

        switch ($data['transport']) {
            case 'gmail':
              $error = $validator->checkIsNotBlank($data['username']);
              if ($error) {
                  $errors['username'] = $error;
              }
              $error = $validator->checkIsNotBlank($data['password']);
              if ($error) {
                  $errors['password'] = $error;
              }
              break;
            case 'smtp':
              $error = $validator->checkIsNotBlank($data['host']);
              if ($error) {
                  $errors['host'] = $error;
              }
              $error = $validator->checkIsValidMailEncryption($data['encryption']);
              if ($error) {
                  $errors['encrytion'] = $error;
              }
              $error = $validator->checkIsValidMailAuthMode($data['auth_mode']);
              if ($error) {
                  $errors['auth_mode'] = $error;
              }

              if (!empty($data['port'])) {
                  $error = $validator->checkIsPositiveNumber($data['port']);
                  if ($error) {
                      $errors['port'] = $error;
                  }
              }
              break;
        }

        if (count($errors) > 0) {
            return $errors;
        }

        $error = $this->testTransport($data);

        if ($error) {
            $errors['transport'] = $error;
        }

        return $errors;
    }

    public function send(Message $message)
    {
        $swiftMessage = (new Swift_Message())
          ->setSubject($message->getAttribute('subject'))
          ->setFrom($message->getAttribute('from'))
          ->setReplyTo($message->getAttribute('reply_to'))
          ->setBody($message->getAttribute('body'), 'text/html')
          ->setBcc($message->getAttribute('bcc'))
          ->setTo($message->getAttribute('to'));

        foreach ($message->getAttribute('attachments') as $attachment) {
            $swiftMessage->attach(
              Swift_Attachment::fromPath(
                  $attachment['path'],
                  $attachment['content_type']
              )
            );
        }

        return $this->mailer->send($swiftMessage) ? true : false;
    }

    public function testTransport($data)
    {
        switch ($data['transport']) {
          case 'smtp':
              return $this->testSmtp($data);
          case 'gmail':
              return $this->testGmail($data);
          case 'sendmail':
              return $this->testSendmail($data);
        }
    }

    private function testSmtp(array $data)
    {
        $port = $data['port'];

        if (empty($port)) {
            $port = 'ssl' === $data['encryption'] ? 465 : 25;
        }

        try {
            Swift_SmtpTransport::newInstance()
            ->setHost($data['host'])
            ->setUsername($data['username'])
            ->setPassword($data['password'])
            ->setAuthMode($data['auth_mode'])
            ->setEncryption($data['encryption'])
            ->setPort($port)
            ->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_SMTP;
        }
    }

    private function testGmail(array $data)
    {
        try {
            Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername($data['username'])
            ->setPassword($data['password'])
            ->setAuthMode('login')
            ->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_GMAIL;
        }
    }

    private function testSendmail(array $data)
    {
        try {
            //allow to configure this
            $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
            $transport->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_SENDMAIL;
        }
    }
}
