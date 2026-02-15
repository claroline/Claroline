<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures;

use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\InstallationBundle\Fixtures\PostInstallInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultToolsData extends AbstractFixture implements PostInstallInterface
{
    private ToolProvider $toolProvider;

    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->toolProvider = $container->get('claroline.provider.tool');
    }

    public function getOrder(): int
    {
        return 1;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadContextTools($manager, PublicContext::getName());
        $this->loadContextTools($manager, DesktopContext::getName());
        $this->loadContextTools($manager, AdministrationContext::getName());

        $manager->flush();
    }

    private function loadContextTools(ObjectManager $manager, string $contextName): void
    {
        $tools = $this->toolProvider->getAvailableTools($contextName);
        foreach ($tools as $order => $tool) {
            $orderedTool = $this->createContextTool($contextName, $tool::getName(), $order);
            $manager->persist($orderedTool);
        }
    }

    private function createContextTool(string $contextName, string $toolName, int $order): OrderedTool
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setContextName($contextName);
        $orderedTool->setOrder($order);
        $orderedTool->setName($toolName);

        return $orderedTool;
    }
}
