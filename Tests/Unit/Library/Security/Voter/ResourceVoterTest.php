<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Entity\User;

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
        $this->markTestSkipped();
        $nodes = $collection->getResources();
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $decoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $type = new \Claroline\CoreBundle\Entity\Resource\ResourceType();

        $nodes[0]->shouldReceive('getCreator')->once()->andReturn('creator_a');
        $token->shouldReceive('getUser')->once()->andReturn('creator_b');

        $nodes[0]->shouldReceive('getResourceType')->andReturn($type);
        $nodes[0]->shouldReceive('getPathForDisplay')->andReturn('/path/to/dir');
        $this->ut->shouldReceive('getRoles')->with($token)->andReturn(array());

        /* the following line doesn't work (why ?)
        $this->maskManager->shouldReceive('getDecoder')->with($type, $parameters[0])->andReturn($decoder);
        */
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

    /**
     * @dataProvider checkActionProvider
     */
    public function testCheckAction(
        $firstWorkspace,
        $secondWorkspace,
        $isWorkspaceManager,
        $firstResourceCreator,
        $secondResourceCreator,
        $userToken,
        $countErrors,
        $mask,
        $decoder
    )
    {
        $voter = $this->getVoter(array('isWorkspaceManager', 'getRoleActionDeniedMessage'));
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->shouldReceive('getUser')->andReturn($userToken);
        $action = 'ACTION';
        $this->maskManager->shouldReceive('getDecoder')->andReturn($decoder);

        if ($decoder) {
            $decoder->shouldReceive('getValue')->andReturn('1');
        }

        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceType->shouldReceive('getName')->andReturn('type');

        $firstNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $secondNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $firstNode->shouldReceive('getCreator')->andReturn($firstResourceCreator);
        $secondNode->shouldReceive('getCreator')->andReturn($secondResourceCreator);
        $firstNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $secondNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $firstNode->shouldReceive('getWorkspace')->andReturn($firstWorkspace);
        $secondNode->shouldReceive('getWorkspace')->andReturn($secondWorkspace);
        $firstNode->shouldReceive('getPathForDisplay')->andReturn('path');
        $secondNode->shouldReceive('getPathForDisplay')->andReturn('path');

        $voter->shouldReceive('isWorkspaceManager')->andReturn($isWorkspaceManager);
        $voter->shouldReceive('getRoleActionDeniedMessage')->andReturn('msg');

        $resources = array($firstNode, $secondNode);

        $this->ut->shouldReceive('getRoles')->andReturn(array());
        $this->repository->shouldReceive('findMaximumRights')->andReturn($mask);

        $this->assertEquals($countErrors, count($voter->checkAction($action, $resources, $token)));
    }

    /**
     * @dataProvider checkCreationProvider
     */
    public function testCheckCreation(
        $countErrors,
        $isWorkspaceManager,
        $creationRights
    )
    {
        $voter = $this->getVoter(array('isWorkspaceManager'));
        $voter->shouldReceive('isWorkspaceManager')->andReturn($isWorkspaceManager);

        $type = 'validType';
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $workspace = new SimpleWorkspace();

        $node->shouldReceive('getPathForDisplay')->andReturn('path');
        $this->translator->shouldReceive('trans')->andReturn('whatever');

        $this->ut->shouldReceive('getRoles')->andReturn(array());
        $this->repository->shouldReceive('findCreationRights')->andReturn($creationRights);

        $this->assertEquals(
            count($voter->checkCreation($type , $node, $token, $workspace)),
            $countErrors
        );
    }

    /**
     * @dataProvider checkMoveProvider
     */
    public function testCheckMove(
        $countErrors,
        $parentWorkspace,
        $firstNodeWorkspace,
        $secondNodeWorkspace,
        $isParentWorkspaceManager
    )
    {
        $this->markTestSkipped();
        $voter = $this->getVoter(array('isWorkspaceManager'));
        $this->ut->shouldReceive('getRoles')->andReturn(array());
        $voter->shouldReceive('isWorkspaceManager')->andReturn($isParentWorkspaceManager);

        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent->shouldReceive('getWorkspace')->andReturn($parentWorkspace);
        $firstNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $secondNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $firstNode->shouldReceive('getWorkspace')->andReturn($firstNodeWorkspace);
        $secondNode->shouldReceive('getWorkspace')->andReturn($secondNodeWorkspace);
        $resources = array($firstNode, $secondNode);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->assertEquals(
            count($voter->checkMove($parent, $resources, $token)),
            $countErrors
        );
    }

    public function testCopy(
        $countErrors
    )
    {

    }

    public function checkMoveProvider()
    {
        $parentWorkspace = new SimpleWorkspace();
        $firstNodeWorkspace = new SimpleWorkspace();
        $secondNodeWorkspace = new SimpleWorkspace();

        return array(
            array(
                //manager can do w/e he wants
                'countErrors' => 0,
                'parentWorkspace' => $parentWorkspace,
                'firstNodeWorkspace' => $parentWorkspace,
                'secondNodeWorkspace' => $parentWorkspace,
                'isParentWorkspaceManager' => true
            ),
            array(
                //manager can do w/e he wants
                'countErrors' => 0,
                'parentWorkspace' => $parentWorkspace,
                'firstNodeWorkspace' => $firstNodeWorkspace,
                'secondNodeWorkspace' => $secondNodeWorkspace,
                'isParentWorkspaceManager' => true
            ),
        );
    }

    public function checkCreationProvider()
    {
        return array(
            //workspace manager can do w/e he want
            array(
                'countErrors' => 0,
                'isWorkspaceManager' => true,
                'creationRights' => array()
            ),
            //There is no creationRights
            array(
                'countErrors' => 1,
                'isWorkspaceManager' => false,
                'creationRights' => array()
            ),
            //wrong creationRights
            array(
                'countErrors' => 1,
                'isWorkspaceManager' => false,
                'creationRights' => array(array('name' => 'invalid'), array('name' => 'notworking'))
            ),
            //that one should work
            array(
                'countErrors' => 0,
                'isWorkspaceManager' => false,
                'creationRights' =>  array(array('name' => 'invalid'), array('name' => 'validType'))
            )
        );
    }

    public function checkActionProvider()
    {
        $firstWorkspace = new SimpleWorkspace();
        $secondWorkspace = new SimpleWorkspace();
        $firstUser = new User();
        $secondUser = new User();
        $decoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');

        return array(
            //workspace manager can do anything
            array(
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $firstWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => null,
                'secondResourceCreator' => null,
                'userToken' => null,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => null
            ),
            //the resourceCreator can do w/e he wants
            array(
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $firstUser,
                'userToken' => $firstUser,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => null
            ),
            //there is no decoder
            array(
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 1,
                'mask' => 31,
                'decoder' => null
            ),
            //the access is granted
            array(
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => $decoder
            ),
            //the access is not granted
            array(
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 2,
                'mask' => 0,
                'decoder' => $decoder
            ),
        );
    }

    private function getVoter(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new ResourceVoter($this->em, $this->translator, $this->ut, $this->maskManager);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Library\Security\Voter\ResourceVoter' . $stringMocked,
            array($this->em, $this->translator, $this->ut, $this->maskManager)
        );
    }
}
