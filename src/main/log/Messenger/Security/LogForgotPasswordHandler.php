<?php

namespace Claroline\LogBundle\Messenger\Security;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Security\Message\ForgotPasswordMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogForgotPasswordHandler implements MessageHandlerInterface
{
    private $em;
    private $translator;
    private $objectManager;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ObjectManager $objectManager
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->objectManager = $objectManager;
    }

    public function __invoke(ForgotPasswordMessage $forgotPasswordMessage): void
    {
        $target = $this->objectManager->getRepository(User::class)->find($forgotPasswordMessage->getTargetId());
        $doer = $this->objectManager->getRepository(User::class)->find($forgotPasswordMessage->getDoerId());

        $logEntry = new SecurityLog();
        $logEntry->setDetails($this->getMessage($this->translator, $doer));
        $logEntry->setEvent($forgotPasswordMessage->getName());
        $logEntry->setTarget($target);
        $logEntry->setDoer($doer);

        $this->em->persist($logEntry);
        $this->em->flush();
    }

    private function getMessage(TranslatorInterface $translator, User $user)
    {
        return $translator->trans('forgotPassword', ['username' => $user->getUsername()], 'security');
    }
}
