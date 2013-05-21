<?php
namespace Claroline\CoreBundle\Library\User;

use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Workspace\Creator as WsCreator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.user.creator")
 */
class Creator
{
    private $em;
    private $trans;
    private $ch;
    private $wsCreator;
    private $personalWsTemplateFile;
    private $ed;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "trans" = @DI\Inject("translator"),
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "wsCreator" = @DI\Inject("claroline.workspace.creator"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "ed"  = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $trans,
        PlatformConfigurationHandler $ch,
        WsCreator $wsCreator,
        $personalWsTemplateFile,
        $ed
    )
    {
        $this->em = $em;
        $this->trans = $trans;
        $this->ch = $ch;
        $this->wsCreator = $wsCreator;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->ed = $ed;
    }

    /**
     * Creates a user. This method will create the user personal workspace
     * and persist the $user.
     *
     * @param User $user
     *
     * @return User
     */
    public function create(User $user)
    {
        $user->addRole($this->em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER'));
        $this->em->persist($user);
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
        //uncomment this line when the templating system is working
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->ch->getParameter('locale_language');
        $this->trans->setLocale($locale);
        $personalWorkspaceName = $this->trans->trans('personal_workspace', array(), 'platform');
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());
        $workspace = $this->wsCreator->createWorkspace($config, $user, false);
        $user->setPersonalWorkspace($workspace);
        $this->em->persist($workspace);

        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $requiredTools[] = $repo->findOneBy(array('name' => 'home'));
        $requiredTools[] = $repo->findOneBy(array('name' => 'resource_manager'));
        $requiredTools[] = $repo->findOneBy(array('name' => 'parameters'));
        $requiredTools[] = $repo->findOneBy(array('name' => 'logs'));

        $i = 1;

        foreach ($requiredTools as $requiredTool) {
            $udt = new DesktopTool();
            $udt->setUser($user);
            $udt->setOrder($i);
            $udt->setTool($requiredTool);
            $i++;
            $this->em->persist($udt);
        }

        $this->em->flush();

        // Log user creation
        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);

        return $user;
    }
}
