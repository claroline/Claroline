<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Event\LogUserLoginEvent;
use Claroline\CoreBundle\Library\User\Creator;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManager;

/**
 * @DI\Service
 */
class AuthenticationSuccessListener
{
    private $securityContext;
    private $eventDispatcher;
    private $creator;
    private $trans;
    private $ch;
    private $personalWsTemplateFile;
    private $em;

    /**
     * @DI\InjectParams({
     *     "context"    = @DI\Inject("security.context"),
     *     "ed"         = @DI\Inject("event_dispatcher"),
     *     "creator" = @DI\Inject("claroline.user.creator"),
     *     "trans" = @DI\Inject("translator"),
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%")
     * })
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(
        SecurityContextInterface $context,
        $ed,
        Creator $creator,
        Translator $trans,
        PlatformConfigurationHandler $ch,
        EntityManager $em,
        $personalWsTemplateFile
    )
    {
        $this->securityContext = $context;
        $this->eventDispatcher = $ed;
        $this->creator = $creator;
        $this->ch = $ch;
        $this->trans = $trans;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->em = $em;
    }

    /**
     * @DI\Observe("security.interactive_login")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onAuthenticationSuccess()
    {
        $user = $this->securityContext->getToken()->getUser();
        $log = new LogUserLoginEvent($user);
        $this->eventDispatcher->dispatch('log', $log);

        if ($user instanceof User && $user->getPersonalWorkspace() === null) {
            $this->creator->setPersonalWorkspace($user);
            $this->em->persist($user);
            $this->em->flush();
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->securityContext->setToken($token);
    }
}