<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Message\CreateRoleChangeLogs;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateRoleChangeLogsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->translator = $translator;
    }

    public function __invoke(CreateRoleChangeLogs $createLog)
    {
        foreach ($createLog->getTargets() as $target) {
            $logEntry = new SecurityLog();

            $logEntry->setDate($createLog->getDate());
            $logEntry->setEvent($createLog->getAction());

            // this should not be done by the handler
            $logEntry->setDetails(
                $this->translator->trans($createLog->getAction().'.desc', ['username' => $target->getUsername(), 'role' => $createLog->getRole()->getName()], 'security')
            );
            $logEntry->setDoer($createLog->getDoer());
            $logEntry->setTarget($target);
            $logEntry->setDoerIp($createLog->getDoerIp());
            $logEntry->setCountry($createLog->getDoerCountry());
            $logEntry->setCity($createLog->getDoerCity());

            $this->om->persist($logEntry);
        }

        $this->om->flush();
    }
}
