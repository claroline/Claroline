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

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
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
        $this->maskManager = $this->mock("Claroline\CoreBundle\Manager\Resource\MaskManager");
        $this->repository = $this->mock("Claroline\CoreBundle\Repository\ResourceRightsRepository");
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');

        $this->em->shouldReceive('getRepository')->once()->with('ClarolineCoreBundle:Resource\ResourceRights')
           ->andReturn($this->repository);

        $this->voter = new ResourceVoter(
          $this->em,
          $this->translator,
          $this->ut,
          $this->maskManager,
          $this->resourceManager,
          $this->workspaceManager
        );
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
    ) {
        $this->markTestSkipped();
        $nodes = $collection->getResources();
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $decoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $type = new ResourceType();

        $nodes[0]->shouldReceive('getCreator')->once()->andReturn('creator_a');
        $token->shouldReceive('getUser')->once()->andReturn('creator_b');

        $nodes[0]->shouldReceive('getResourceType')->andReturn($type);
        $nodes[0]->shouldReceive('getPathForDisplay')->andReturn('/path/to/dir');
        $this->ut->shouldReceive('getRoles')->with($token)->andReturn([]);

        /* the following line doesn't work (why ?)
        $this->maskManager->shouldReceive('getDecoder')->with($type, $parameters[0])->andReturn($decoder);
        */
        $this->maskManager->shouldReceive('getDecoder')->with()->andReturn($decoder);
        $decoder->shouldReceive('getValue')->andReturn($decoderValue);
        $this->repository->shouldReceive('findMaximumRights')->with([], $nodes[0])->andReturn($maskValue);
        $this->translator->shouldReceive('trans')->andReturn("error for {$parameters[0]}");
        $this->assertEquals($voterResult, $this->voter->vote($token, $collection, $parameters));
    }

    public function voterProvider()
    {
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');

        return [
            [
                'collection' => new ResourceCollection([$node]),
                'voterResult' => VoterInterface::ACCESS_DENIED,
                'parameters' => ['custom'],
                'decoderValue' => 1,
                'maskValue' => 0,
            ],
            [
                'collection' => new ResourceCollection([$node]),
                'voterResult' => VoterInterface::ACCESS_GRANTED,
                'parameters' => ['custom'],
                'decoderValue' => 1,
                'maskValue' => 1,
            ],
        ];
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
    ) {
        $this->markTestSkipped();
        $voter = $this->getVoter(['isWorkspaceManager', 'getRoleActionDeniedMessage']);
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

        $resources = [$firstNode, $secondNode];

        $this->ut->shouldReceive('getRoles')->andReturn([]);
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
    ) {
        $this->markTestSkipped();
        $voter = $this->getVoter(['isWorkspaceManager']);
        $voter->shouldReceive('isWorkspaceManager')->andReturn($isWorkspaceManager);

        $type = 'validType';
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $workspace = new Workspace();

        $node->shouldReceive('getPathForDisplay')->andReturn('path');
        $this->translator->shouldReceive('trans')->andReturn('whatever');

        $this->ut->shouldReceive('getRoles')->andReturn([]);
        $this->repository->shouldReceive('findCreationRights')->andReturn($creationRights);

        $this->assertEquals(
            count($voter->checkCreation($type, $node, $token, $workspace)),
            $countErrors
        );
    }

    /**
     * @dataProvider checkMoveProvider
     */
    public function testCheckMove(
        $countErrors,
        $createErrors,
        $copyErrors,
        $deleteErrors
    ) {
        $workspace = new Workspace();
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceType->shouldReceive('getName')->andReturn('type');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent->shouldReceive('getWorkspace')->andReturn($workspace);
        $firstNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $secondNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $firstNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $secondNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $nodes = [$firstNode, $secondNode];

        $voter = $this->getVoter(['checkCreation', 'checkAction', 'checkCopy']);
        $voter->shouldReceive('checkCreation')->andReturn($createErrors);
        $voter->shouldReceive('checkAction')->andReturn($deleteErrors);
        $voter->shouldReceive('checkCopy')->andReturn($copyErrors);

        $this->assertEquals($countErrors, count($voter->checkMove($parent, $nodes, $token)));
    }

    /**
     * @dataProvider checkCopyProvider
     */
    public function testCheckCopy(
        $countErrors,
        $createErrors,
        $copyErrors
    ) {
        $workspace = new Workspace();
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceType->shouldReceive('getName')->andReturn('type');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent->shouldReceive('getWorkspace')->andReturn($workspace);
        $firstNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $secondNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $firstNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $secondNode->shouldReceive('getResourceType')->andReturn($resourceType);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $nodes = [$firstNode, $secondNode];

        $voter = $this->getVoter(['checkCreation', 'checkAction']);
        $voter->shouldReceive('checkCreation')->andReturn($createErrors);
        $voter->shouldReceive('checkAction')->andReturn($copyErrors);

        $this->assertEquals($countErrors, count($voter->checkCopy($parent, $nodes, $token)));
    }

    /**
     * @dataProvider IPValidationProvider
     */
    public function testIPValidation($allowed, $current, $expected)
    {
        $voter = $this->getVoter();
        $this->assertEquals($voter->validateIP($allowed, $current), $expected);
    }

    public function IPValidationProvider()
    {
        return [
        [
          'allowed' => [
            '100.100.100.100',
            '123.123.123.123',
          ],
          'current' => '123.123.123.123',
          'expectedAnswer' => true,
        ],
        [
          'allowed' => [
            '100.100.100.100',
            '123.123.123.123',
          ],
          'current' => '124.123.123.123',
          'expectedAnswer' => false,
        ],
        [
          'allowed' => [],
          'current' => '123.123.123.123',
          'expectedAnswer' => false,
        ],
        [
          'allowed' => [
            '100.100.100.100',
            '121.125.123.x',
          ],
          'current' => '121.125.123.2',
          'expectedAnswer' => true,
        ],
        [
          'allowed' => [
            '100.124.x.x',
            '121.x.123.x',
          ],
          'current' => '100.124.0.2',
          'expectedAnswer' => true,
        ],
        [
          'allowed' => [
            '100.124.x.x',
            '121.x.123.x',
          ],
          'current' => '100.2.0.2',
          'expectedAnswer' => false,
        ],
        [
          'allowed' => [
            '100.124.x.x',
            'x.x.x.x',
          ],
          'current' => '100.2.0.2',
          'expectedAnswer' => true,
        ],
      ];
    }

    public function checkCopyProvider()
    {
        return [
            //valid
            [
                'countErrors' => 0,
                'copyErrors' => [],
                'createErrors' => [],
            ],
            //cannot copy
            [
                'countErrors' => 2,
                'copyErrors' => ['error'],
                'createErrors' => [],
            ],
            //cannot create
            [
                'countErrors' => 1,
                'copyErrors' => [],
                'createErrors' => ['error'],
            ],
        ];
    }

    public function checkMoveProvider()
    {
        return [
            //valid
            [
                'countErrors' => 0,
                'copyErrors' => [],
                'createErrors' => [],
                'deleteErrors' => [],
            ],
            //cannot copy
            [
                'countErrors' => 2,
                'copyErrors' => ['error'],
                'createErrors' => [],
                'deleteErrors' => [],
            ],
            //cannot create
            [
                'countErrors' => 1,
                'copyErrors' => [],
                'createErrors' => ['error'],
                'deleteErrors' => [],
            ],
            //delete errors
            [
                'countErrors' => 1,
                'copyErrors' => [],
                'createErrors' => [],
                'deleteErrors' => ['error'],
            ],
        ];
    }

    public function checkCreationProvider()
    {
        return [
            //workspace manager can do w/e he want
            [
                'countErrors' => 0,
                'isWorkspaceManager' => true,
                'creationRights' => [],
            ],
            //There is no creationRights
            [
                'countErrors' => 1,
                'isWorkspaceManager' => false,
                'creationRights' => [],
            ],
            //wrong creationRights
            [
                'countErrors' => 1,
                'isWorkspaceManager' => false,
                'creationRights' => [['name' => 'invalid'], ['name' => 'notworking']],
            ],
            //that one should work
            [
                'countErrors' => 0,
                'isWorkspaceManager' => false,
                'creationRights' => [['name' => 'invalid'], ['name' => 'validType']],
            ],
        ];
    }

    public function checkActionProvider()
    {
        $firstWorkspace = new Workspace();
        $secondWorkspace = new Workspace();
        $firstUser = new User();
        $secondUser = new User();
        $decoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');

        return [
            //workspace manager can do anything
            [
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $firstWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => null,
                'secondResourceCreator' => null,
                'userToken' => null,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => null,
            ],
            //the resourceCreator can do w/e he wants
            [
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $firstUser,
                'userToken' => $firstUser,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => null,
            ],
            //there is no decoder
            [
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 1,
                'mask' => 31,
                'decoder' => null,
            ],
            //the access is granted
            [
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 0,
                'mask' => 31,
                'decoder' => $decoder,
            ],
            //the access is not granted
            [
                'firstWorkspace' => $firstWorkspace,
                'secondWorkspace' => $secondWorkspace,
                'isWorkspaceManager' => true,
                'firstResourceCreator' => $firstUser,
                'secondResourceCreator' => $secondUser,
                'userToken' => $firstUser,
                'countErrors' => 2,
                'mask' => 0,
                'decoder' => $decoder,
            ],
        ];
    }

    private function getVoter(array $mockedMethods = [])
    {
        if (count($mockedMethods) === 0) {
            return new ResourceVoter(
              $this->em,
              $this->translator,
              $this->ut,
                $this->maskManager,
              $this->resourceManager,
              $this->workspaceManager
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Library\Security\Voter\ResourceVoter'.$stringMocked,
            [$this->em, $this->translator, $this->ut, $this->maskManager, $this->resourceManager, $this->workspaceManager]
        );
    }
}
