<?php
namespace Claroline\CoreBundle\Library\User;

use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Workspace\Creator as WsCreator;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class Creator
{
    private $em;
    private $trans;
    private $ch;
    private $wsCreator;

    public function __construct(EntityManager $em, Translator $trans, PlatformConfigurationHandler $ch, WsCreator $wsCreator)
    {
        $this->em = $em;
        $this->trans = $trans;
        $this->ch = $ch;
        $this->wsCreator = $wsCreator;
    }

    public function create($user)
    {
        $this->em->persist($user);
        $config = new Configuration();
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->ch->getParameter('locale_language');
        $this->trans->setLocale($locale);
        $personalWorkspaceName = $this->trans->trans('personal_workspace', array(), 'platform');
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());
        $workspace = $this->wsCreator->createWorkspace($config, $user);
        $workspace->setType(AbstractWorkspace::PERSONNAL);
        $user->addRole($workspace->getManagerRole());
        $user->setPersonalWorkspace($workspace);
        $this->em->persist($workspace);
        $this->em->flush();

        return $user;
    }
}
