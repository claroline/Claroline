<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;

class Updater040201 extends Updater
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate()
    {
        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository('ClarolineCoreBundle:User');

        /** @var \Doctrine\ORM\Query $usersQuery */
        $usersQuery = $userRepository->findAllEnabledUsers(false);
        $users = $usersQuery->iterate();

        /** @var \Claroline\CoreBundle\Repository\OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->entityManager->getRepository('ClarolineCoreBundle:Tool\OrderedTool');

        $toolName = 'my_portfolios';
        $tool = $this->entityManager->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($toolName);

        if (null === $tool) {
            $tool = new Tool();
            $tool
                ->setDisplayableInWorkspace(false)
                ->setName($toolName)
                ->setClass('list-alt');

            $this->entityManager->persist($tool);
            $this->entityManager->flush();
        }

        $countUser = $userRepository->countAllEnabledUsers();
        $index = 0;

        $this->log(sprintf("%d ordered tools to add for users - %s", $countUser, date('Y/m/d H:i:s')));

        foreach ($users as $row) {
            $user = $row[0];
            /** @var \Claroline\CoreBundle\Entity\Tool\OrderedTool $orderedTools */
            $orderedTool = $orderedToolRepo->findOneBy([
                'tool' => $tool,
                'user' => $user,
                'type' => 1
            ]);

            if (null === $orderedTool) {
                $orderedTool = new OrderedTool();
                $orderedTool->setName($toolName);
                $orderedTool->setTool($tool);
                $orderedTool->setUser($user);
                $orderedTool->setVisibleInDesktop(true);
                $orderedTool->setOrder(1);
                $orderedTool->setType(1);
                $this->entityManager->persist($orderedTool);
                $index++;

                if ($index % 200 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear($orderedTool);
                    $this->log(sprintf("    %d ordered tools added - %s", 200, date('Y/m/d H:i:s')));
                }
            }
        }
        if ($index % 200 !== 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $this->log(sprintf("    %d ordered tools added - %s", $index % 200, date('Y/m/d H:i:s')));
        }
    }
}