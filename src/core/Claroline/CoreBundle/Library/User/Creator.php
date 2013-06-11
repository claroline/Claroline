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
    const BATCH_SIZE = 5;

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
    public function create(User $user, $autoflush = true)
    {
        $user->addRole($this->em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER'));
        $this->em->persist($user);
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
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
     *     [4] => 'mail' (optionnal)
     * )
     * @param array $users
     */
    public function import($users)
    {
        $role = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
        $i = $j = 0;
        var_dump($users);

        foreach ($users as $user) {
            $userEntity = new User();
            $userEntity->setFirstName($user[0]);
            $userEntity->setLastName($user[1]);
            $userEntity->setUsername($user[2]);
            $userEntity->setPassword($user[3]);

            if (isset($user[4])) {
                $userEntity->setMail($user[4]);
            }

            $userEntity->addRole($role);
            $user = $this->create($userEntity, false);
            $this->em->persist($user);

            //echo ("UOW[{$this->em->getUnitOfWork()->size()}]".PHP_EOL);

            if (($i % self::BATCH_SIZE) === 0) {
                $j++;
                $this->em->flush();
                $this->em->clear();
                //echo ("batch [{$j}] | users [{$i}] | UOW  [{$this->em->getUnitOfWork()->size()}]".PHP_EOL);
                $role = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => 'ROLE_USER'));
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();
    }
}
