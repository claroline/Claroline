<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceVoterTest extends MockeryTestCase
{
    private $em;
    private $repository;
    private $translator;
    private $ut;
    private $maskManager;
    private $voter;

    public function setUp()
    {
        parent::setUp();

        $this->em = $this->mock("Doctrine\ORM\EntityManager");
        $this->translator = $this->mock("Symfony\Component\Translation\Translator");
        $this->ut = $this->mock("Claroline\CoreBundle\Library\Security\Utilities");
        $this->maskManager = $this->mock("Claroline\CoreBundle\Manager\MaskManager");
        $this->repository = $this->mock("Claroline\CoreBundle\Repository\ResourceRightsRepository");
        $this->em->shouldReceive('getRepository')->once()->with('ClarolineCoreBundle:Resource\ResourceRights')
            ->andReturn($this->repository);
        $this->voter = new ResourceVoter($this->em, $this->translator, $this->ut, $this->maskManager);
    }

    /**
     * @dataProvider voterProvider
     */
    public function testVoteWithoutResourceCreationWithDecoder(
        $collection,
        $voterResult,
        $parameters,
        $decoderValue,
        $maskValue
    )
    {
        $nodes = $collection->getResources();
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $decoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $type = new \Claroline\CoreBundle\Entity\Resource\ResourceType();

        $nodes[0]->shouldReceive('getCreator')->once()->andReturn('creator_a');
        $token->shouldReceive('getUser')->once()->andReturn('creator_b');

        $nodes[0]->shouldReceive('getResourceType')->andReturn($type);
        $nodes[0]->shouldReceive('getPathForDisplay')->andReturn('/path/to/dir');
        $this->ut->shouldReceive('getRoles')->with($token)->andReturn(array());
        //the following line doesn't work (why ?)
//        $this->maskManager->shouldReceive('getDecoder')->with($type, $parameters[0])->andReturn($decoder);
        $this->maskManager->shouldReceive('getDecoder')->with()->andReturn($decoder);
        $decoder->shouldReceive('getValue')->andReturn($decoderValue);
        $this->repository->shouldReceive('findMaximumRights')->with(array(), $nodes[0])->andReturn($maskValue);
        $this->translator->shouldReceive('trans')->andReturn("error for {$parameters[0]}");
        $this->assertEquals($voterResult, $this->voter->vote($token, $collection, $parameters));
    }

    public function voterProvider()
    {
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');

        return array(
            array(
                'collection'   => new ResourceCollection(array($node)),
                'voterResult'  => VoterInterface::ACCESS_DENIED,
                'parameters'   => array('custom'),
                'decoderValue' => 1,
                'maskValue'    => 0
            ),
            array(
                'collection'   => new ResourceCollection(array($node)),
                'voterResult'  => VoterInterface::ACCESS_GRANTED,
                'parameters'   => array('custom'),
                'decoderValue' => 1,
                'maskValue'    => 1
            )
        );
    }
}