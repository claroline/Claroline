<?php
namespace Claroline\CoreBundle\Library\User;

use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.user.creator")
 */
class Creator
{
    const BATCH_SIZE = 150;

    private $em;
    private $trans;
    private $ch;
    private $wsCreator;
    private $personalWsTemplateFile;
    private $ed;
    private $sc;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "trans" = @DI\Inject("translator"),
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "wsCreator" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "sc" = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $trans,
        PlatformConfigurationHandler $ch,
        WorkspaceManager $wsCreator,
        $toolManager,
        $personalWsTemplateFile,
        $ed,
        $sc
    )
    {
        $this->em = $em;
        $this->trans = $trans;
        $this->ch = $ch;
        $this->wsCreator = $wsCreator;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->ed = $ed;
        $this->sc = $sc;
        $this->toolManager = $toolManager;
    }

    /**
     * Creates a user. This method will create the user personal workspace
     * and persist the $user.
     *
     * @param User $user
     *
     * @return User
     */
    public function create(User $user, $autoflush = true)
    {
        $user->addRole($this->em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER'));
        $this->em->persist($user);
        $this->setPersonalWorkspace($user);
        $this->addRequiredTools($user, $this->findRequiredTools());

        if ($autoflush) {
            $this->em->flush();
        }

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);

        return $user;
    }

    /**
     * Expects an array of users. Each user must be defined this way:
     * array(
     *     [0] => 'firstname',
     *     [1] => 'lastname',
     *     [2] => 'username',
     *     [3] => 'password',
     *     [4] => 'code',
     *     [5] => 'mail' (optionnal)
     * )
     * @param array $users
     */
    public function import($users)
    {
        $role = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
        $requiredTools = $this->findRequiredTools();
        $i = $j = 0;

        foreach ($users as $user) {
            $userEntity = new User();
            $userEntity->addRole($role);
            $userEntity->setFirstName($user[0]);
            $userEntity->setLastName($user[1]);
            $userEntity->setUsername($user[2]);
            $userEntity->setPlainPassword($user[3]);
            $userEntity->setAdministrativeCode($user[4]);

            if (isset($user[5])) {
                $userEntity->setMail($user[5]);
            }

            $this->addRequiredTools($userEntity, $requiredTools);
            $this->em->persist($userEntity);
            $log = new LogUserCreateEvent($userEntity);
            $this->ed->dispatch('log', $log);

            if (($i % self::BATCH_SIZE) === 0) {
                $j++;

                $this->em->flush();
                $this->em->clear();

                echo ("batch [{$j}] | users [{$i}] | UOW  [{$this->em->getUnitOfWork()->size()}]".PHP_EOL);

                $role = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
                $requiredTools = $this->findRequiredTools();
                $doer = $this->em->getRepository('ClarolineCoreBundle:User')
                    ->findOneByUsername($this->sc->getToken()->getUser()->getUsername());
                $this->em->merge($doer);
                $this->sc->getToken()->setUser($doer);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();
    }

    private function addRequiredTools(User $user, array $requiredTools)
    {
        $i = 1;

        foreach ($requiredTools as $requiredTool) {
            $this->toolManager->addDesktopTool($requiredTool, $user, $i, $requiredTool->getName());
            $i++;
        }

    }

    private function findRequiredTools()
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $requiredTools[] = $repo->findOneBy(array('name' => 'home'));
        $requiredTools[] = $repo->findOneBy(array('name' => 'resource_manager'));
        $requiredTools[] = $repo->findOneBy(array('name' => 'parameters'));

        return $requiredTools;
    }

    public function setPersonalWorkspace(User $user)
    {
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->ch->getParameter('locale_language');
        $this->trans->setLocale($locale);
        $personalWorkspaceName = $this->trans->trans('personal_workspace', array(), 'platform');
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());
        $workspace = $this->wsCreator->create($config, $user, false);
        $this->em->persist($workspace);
        $user->setPersonalWorkspace($workspace);
    }
}
