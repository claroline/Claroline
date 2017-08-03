<?php

namespace HeVinci\CompetencyBundle\Security;

use HeVinci\CompetencyBundle\Util\UnitTestCase;

class AdminToolAccessEvaluatorTest extends UnitTestCase
{
    private $context;
    private $repo;
    private $evaluator;

    protected function setUp()
    {
        $this->context = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->repo = $this->mock('Doctrine\ORM\EntityRepository');
        $em = $this->mock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->any())
            ->method('getRepository')
            ->with('ClarolineCoreBundle:Tool\AdminTool')
            ->willReturn($this->repo);
        $this->evaluator = new AdminToolAccessEvaluator($this->context, $em);
    }

    /**
     * @expectedException \LogicException
     */
    public function testEvaluatorExpectsTheToolToExist()
    {
        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'foo'])
            ->willReturn(null);
        $this->evaluator->canOpenAdminTool('foo');
    }

    public function testEvaluatorDelegatesToSecurityContext()
    {
        $this->repo->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['name' => 'foo'])
            ->willReturn('FooTool');
        $this->context->expects($this->exactly(2))
            ->method('isGranted')
            ->with('OPEN', 'FooTool')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->assertTrue($this->evaluator->canOpenAdminTool('foo'));
        $this->assertFalse($this->evaluator->canOpenAdminTool('foo'));
    }
}
