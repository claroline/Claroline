<?php

namespace Claroline\CoreBundle\Repository\Log;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogRepositoryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /** @var \Claroline\CoreBundle\Repository\Log\LogRepository */
    private $logRepository;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->logRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function testFindByWorkspaceBadgeRuleAndUserWithNoWorkspace()
    {
        $badgeRule = new BadgeRule();
        $user      = new User();
        $query     = $this->logRepository->findByWorkspaceBadgeRuleAndUser(null, $badgeRule, $user, false);
        $expectedQuery = 'SELECT c0_.id AS id0, c0_.action AS action1, c0_.date_log AS date_log2, c0_.short_date_log AS short_date_log3, c0_.details AS details4, c0_.doer_type AS doer_type5, c0_.doer_ip AS doer_ip6, c0_.tool_name AS tool_name7, c0_.is_displayed_in_admin AS is_displayed_in_admin8, c0_.is_displayed_in_workspace AS is_displayed_in_workspace9, c0_.doer_id AS doer_id10, c0_.receiver_id AS receiver_id11, c0_.receiver_group_id AS receiver_group_id12, c0_.owner_id AS owner_id13, c0_.workspace_id AS workspace_id14, c0_.resourceNode_id AS resourceNode_id15, c0_.resource_type_id AS resource_type_id16, c0_.role_id AS role_id17 FROM claro_log c0_ WHERE c0_.action = ? AND c0_.doer_id = ? ORDER BY c0_.date_log ASC';
        $this->assertEquals($expectedQuery, $query->getSQL());

        $actionParameter    = new Parameter('action', $badgeRule->getAction());
        $doerParameter      = new Parameter('doer', $user);
        $expectedParameters = new ArrayCollection();
        $expectedParameters->add($actionParameter);
        $expectedParameters->add($doerParameter);

        $this->assertEquals($expectedParameters, $query->getParameters());
    }

    public function testFindByWorkspaceBadgeRuleAndUserWithWorkspace()
    {
        $badgeRule = new BadgeRule();
        $user      = new User();
        $workspace = new SimpleWorkspace();

        $query     = $this->logRepository->findByWorkspaceBadgeRuleAndUser($workspace, $badgeRule, $user, false);
        $expectedQuery = 'SELECT c0_.id AS id0, c0_.action AS action1, c0_.date_log AS date_log2, c0_.short_date_log AS short_date_log3, c0_.details AS details4, c0_.doer_type AS doer_type5, c0_.doer_ip AS doer_ip6, c0_.tool_name AS tool_name7, c0_.is_displayed_in_admin AS is_displayed_in_admin8, c0_.is_displayed_in_workspace AS is_displayed_in_workspace9, c0_.doer_id AS doer_id10, c0_.receiver_id AS receiver_id11, c0_.receiver_group_id AS receiver_group_id12, c0_.owner_id AS owner_id13, c0_.workspace_id AS workspace_id14, c0_.resourceNode_id AS resourceNode_id15, c0_.resource_type_id AS resource_type_id16, c0_.role_id AS role_id17 FROM claro_log c0_ WHERE c0_.action = ? AND c0_.doer_id = ? AND c0_.workspace_id = ? ORDER BY c0_.date_log ASC';
        $this->assertEquals($expectedQuery, $query->getSQL());

        $actionParameter         = new Parameter('action', $badgeRule->getAction());
        $doerParameter           = new Parameter('doer', $user);
        $workspaceParameter      = new Parameter('workspace', $workspace);
        $expectedParameters = new ArrayCollection();
        $expectedParameters->add($actionParameter);
        $expectedParameters->add($doerParameter);
        $expectedParameters->add($workspaceParameter);

        $this->assertEquals($expectedParameters, $query->getParameters());
    }
}
